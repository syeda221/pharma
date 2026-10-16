<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerPayment;
use App\Models\JournalEntry;
use App\Services\BalanceService;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BulkCustomerPaymentController extends Controller
{
    /**
     * Show the Bulk Customer Payment / Recovery Entry Form
     */
    public function create()
    {
        $user = auth()->user();
        if (!$user || (!$user->can('bulk.customer.payments.create') && !$user->hasRole('Super Admin') && !$user->hasRole('admin') && !$user->hasRole('Admin'))) {
            abort(403, 'Unauthorized action. You do not have permission to create Bulk Customer Recovery.');
        }

        $customers = Customer::where(function ($q) {
            $q->where('status', 'active')
              ->orWhere('status', 1)
              ->orWhere('status', '1')
              ->orWhereNull('status')
              ->orWhere('status', '');
        })
        ->orderBy('customer_name', 'asc')
        ->select('id', 'customer_name', 'customer_id', 'opening_balance', 'mobile', 'address')
        ->get();

        $balanceService = app(BalanceService::class);
        $customerList = $customers->map(function ($c) use ($balanceService) {
            // Get accurate balance
            $bal = $balanceService->getCustomerBalance($c);
            return [
                'id' => $c->id,
                'name' => $c->customer_name,
                'code' => $c->customer_id ?? ('CUST-' . $c->id),
                'balance' => (float) $bal,
                'mobile' => $c->mobile ?? '',
            ];
        })->values();

        // Fetch payment heads (Cash & Bank) along with their active accounts for dynamic dependent row dropdowns
        $paymentHeads = \App\Models\AccountHead::where(function($q) {
            $q->whereIn(DB::raw('LOWER(name)'), ['cash', 'bank', 'cash in hand'])
              ->orWhere('name', 'Cash')
              ->orWhere('name', 'Bank');
        })
        ->with(['accounts' => function($q) {
            $q->where('status', 1)->orderBy('title', 'asc');
        }])
        ->orderByRaw("CASE WHEN LOWER(name) LIKE '%cash%' THEN 1 ELSE 2 END")
        ->get();

        $headsData = $paymentHeads->map(function ($h) {
            return [
                'id' => $h->id,
                'name' => $h->name,
                'accounts' => $h->accounts->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'title' => $a->title,
                        'code' => $a->account_code ?: $a->type,
                        'balance' => (float) ($a->current_balance ?? 0),
                    ];
                })->values(),
            ];
        })->values();

        $defaultBatchNo = CustomerPayment::generateBatchNo();
        $defaultDate = date('Y-m-d');

        return view('admin_panel.customers.bulk_payment', compact(
            'customerList',
            'headsData',
            'defaultBatchNo',
            'defaultDate'
        ));
    }

    /**
     * AJAX endpoint: Get real-time customer balance
     */
    public function getCustomerBalance($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $balanceService = app(BalanceService::class);
        $balance = $balanceService->getCustomerBalance($customer);

        return response()->json([
            'success' => true,
            'customer_id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'balance' => (float) $balance,
        ]);
    }

    /**
     * Store the Bulk Payments Batch
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('bulk.customer.payments.create') && !$user->hasRole('Super Admin') && !$user->hasRole('admin') && !$user->hasRole('Admin'))) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. You do not have permission to create Bulk Customer Recovery.',
            ], 403);
        }

        $request->validate([
            'payment_date' => 'required|date',
            'account_id'   => 'nullable|exists:accounts,id',
            'batch_note'   => 'nullable|string|max:500',
            'payments'     => 'required|array|min:1',
            'payments.*.customer_id'    => 'required|exists:customers,id',
            'payments.*.amount'         => 'required|numeric|min:0.01',
            'payments.*.payment_method' => 'required|string',
            'payments.*.account_id'     => 'required|exists:accounts,id',
            'payments.*.cheque_no'      => 'nullable|string|max:100',
            'payments.*.cheque_date'    => 'nullable|date',
            'payments.*.note'           => 'nullable|string|max:500',
            'attachments.*'             => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $userId = Auth::id() ?? 1;
        $paymentDate = $request->payment_date;
        $batchNote = $request->batch_note;

        // Generate unique batch number for this entire collection
        $batchNo = CustomerPayment::generateBatchNo();

        $createdPayments = [];
        $balanceService = app(BalanceService::class);
        $journalService = app(JournalEntryService::class);
        $arId = $balanceService->getAccountsReceivableId();

        DB::beginTransaction();
        try {
            foreach ($request->payments as $index => $row) {
                $customerId = (int) $row['customer_id'];
                $amount = (float) $row['amount'];
                $method = $row['payment_method'] ?? 'Cash';
                $rowAccountId = !empty($row['account_id']) ? (int) $row['account_id'] : null;
                $chequeNo = $row['cheque_no'] ?? null;
                $chequeDate = !empty($row['cheque_date']) ? $row['cheque_date'] : null;
                $rowNote = $row['note'] ?? null;

                // Find Account title for ledger & journal descriptions
                $accountObj = $rowAccountId ? Account::find($rowAccountId) : null;
                $accTitle = $accountObj ? $accountObj->title : '';

                // Handle file attachment for this specific row (if uploaded)
                $attachmentPath = null;
                if ($request->hasFile("attachments.{$index}")) {
                    $file = $request->file("attachments.{$index}");
                    if ($file->isValid()) {
                        $attachmentPath = $file->store('customer_receipts/' . date('Y/m'), 'public');
                    }
                }

                // 1. Generate unique sequential Receipt Number
                $receiptNo = CustomerPayment::generateReceiptNo();

                // 2. Fetch Customer & calculate accurate previous balance
                $customer = Customer::lockForUpdate()->find($customerId);
                $latestLedger = CustomerLedger::where('customer_id', $customerId)->latest('id')->first();
                $prevBalance = $latestLedger ? (float) $latestLedger->closing_balance : (float) ($customer->opening_balance ?? 0);
                $closingBalance = $prevBalance - $amount;

                // Combine notes
                $combinedNote = $rowNote ?: ($batchNote ?: 'Recovery Payment');

                // 3. Create CustomerPayment record
                $payment = CustomerPayment::create([
                    'batch_no'         => $batchNo,
                    'receipt_no'       => $receiptNo,
                    'customer_id'      => $customerId,
                    'admin_or_user_id' => $userId,
                    'account_id'       => $rowAccountId,
                    'amount'           => $amount,
                    'payment_method'   => $method,
                    'payment_date'     => $paymentDate,
                    'cheque_no'        => $chequeNo,
                    'cheque_date'      => $chequeDate,
                    'note'             => $combinedNote,
                    'attachment'       => $attachmentPath,
                ]);

                // 4. Create CustomerLedger record (Preserve complete historical trail)
                CustomerLedger::create([
                    'customer_id'      => $customerId,
                    'admin_or_user_id' => $userId,
                    'previous_balance' => $prevBalance,
                    'opening_balance'  => 0,
                    'closing_balance'  => $closingBalance,
                    'description'      => "Bulk Receipt #{$batchNo} [{$receiptNo}] - {$method}" . ($accTitle ? " ({$accTitle})" : "") . ": " . ($rowNote ?: 'Payment Received'),
                    'created_at'       => $paymentDate . ' ' . date('H:i:s'),
                ]);

                // 5. Update Customer reminder dates
                if ($customer) {
                    $updateData = ['reminder_snoozed_at' => null];
                    if ($closingBalance > 0 && !$customer->reminder_day) {
                        $updateData['payment_reminder_date'] = date('Y-m-d', strtotime('+7 days', strtotime($paymentDate)));
                    }
                    $customer->update($updateData);
                }

                // 6. Post to financial accounts for this row's chosen deposit account
                if ($rowAccountId && $arId) {
                    try {
                        // Debit Deposit Account (Cash / Bank) - Belongs to company ledger, NO customer party
                        $journalService->recordEntry(
                            $payment,
                            $rowAccountId,
                            $amount,
                            0,
                            "Recovery Receipt [{$receiptNo}] - {$customer->customer_name}" . ($accTitle ? " ({$accTitle})" : ""),
                            $paymentDate,
                            null
                        );

                        // Credit Accounts Receivable (Customer) - Customer is credited, linked to customer party
                        $journalService->recordEntry(
                            $payment,
                            $arId,
                            0,
                            $amount,
                            "Payment Received [{$receiptNo}] - {$customer->customer_name}",
                            $paymentDate,
                            $customer
                        );
                    } catch (\Exception $jEx) {
                        Log::warning("Journal entry skipped for Bulk Customer Payment #{$payment->id}: " . $jEx->getMessage());
                    }
                }

                $createdPayments[] = [
                    'id' => $payment->id,
                    'receipt_no' => $receiptNo,
                    'customer_name' => $customer->customer_name ?? 'N/A',
                    'amount' => $amount,
                    'receipt_url' => route('customer-payments.receipt', $payment->id),
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk payment batch saved successfully.',
                'batch_no' => $batchNo,
                'batch_print_url' => route('customer-payments.batch-print', $batchNo),
                'history_url' => route('customer-payments.batches'),
                'count' => count($createdPayments),
                'payments' => $createdPayments,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk Customer Payment Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save payments: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show Batch Payments History & Listing
     */
    public function batchHistory(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('bulk.customer.payments.view') && !$user->hasRole('Super Admin') && !$user->hasRole('admin') && !$user->hasRole('Admin'))) {
            abort(403, 'Unauthorized action. You do not have permission to view Customer Recovery Batches.');
        }

        $query = CustomerPayment::with(['customer', 'account', 'user'])
            ->whereNotNull('batch_no')
            ->orderBy('id', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('batch_no')) {
            $query->where('batch_no', 'like', '%' . $request->batch_no . '%');
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $payments = $query->paginate(30);

        // Grouping summary by batch
        $batchSummary = CustomerPayment::whereNotNull('batch_no')
            ->select(
                'batch_no',
                'payment_date',
                DB::raw('COUNT(id) as total_customers'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('batch_no', 'payment_date')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        $customers = Customer::orderBy('customer_name')->select('id', 'customer_name')->get();

        return view('admin_panel.customers.batch_payments_list', compact('payments', 'batchSummary', 'customers'));
    }

    /**
     * Print Individual Customer Receipt Voucher (80mm Thermal & A5)
     */
    public function singleReceipt($id)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('bulk.customer.payments.view') && !$user->hasRole('Super Admin') && !$user->hasRole('admin') && !$user->hasRole('Admin'))) {
            abort(403, 'Unauthorized action. You do not have permission to view Customer Recovery Receipts.');
        }

        $payment = CustomerPayment::with(['customer', 'account', 'user'])->findOrFail($id);

        // Get ledger snapshot around this payment
        $ledgerEntry = CustomerLedger::where('customer_id', $payment->customer_id)
            ->where('description', 'like', "%{$payment->receipt_no}%")
            ->first();

        $previousBalance = $ledgerEntry ? (float) $ledgerEntry->previous_balance : 0;
        $currentPaid = (float) $payment->amount;
        $closingBalance = $ledgerEntry ? (float) $ledgerEntry->closing_balance : ($previousBalance - $currentPaid);

        $amountInWords = $this->numberToWords($currentPaid);

        return view('admin_panel.customers.receipt_single', compact(
            'payment',
            'previousBalance',
            'currentPaid',
            'closingBalance',
            'amountInWords'
        ));
    }

    /**
     * Print Consolidated Batch Recovery Sheet (A4)
     */
    public function batchPrint($batch_no)
    {
        $user = auth()->user();
        if (!$user || (!$user->can('bulk.customer.payments.view') && !$user->hasRole('Super Admin') && !$user->hasRole('admin') && !$user->hasRole('Admin'))) {
            abort(403, 'Unauthorized action. You do not have permission to print Batch Recovery Sheet.');
        }

        $payments = CustomerPayment::with(['customer', 'account', 'user'])
            ->where('batch_no', $batch_no)
            ->orderBy('id', 'asc')
            ->get();

        if ($payments->isEmpty()) {
            abort(404, 'Batch Recovery sheet not found.');
        }

        $first = $payments->first();
        $totalAmount = $payments->sum('amount');
        $amountInWords = $this->numberToWords($totalAmount);

        // Attach ledger balance snapshot for each payment
        foreach ($payments as $p) {
            $ledger = CustomerLedger::where('customer_id', $p->customer_id)
                ->where('description', 'like', "%{$p->receipt_no}%")
                ->first();
            $p->prev_balance = $ledger ? (float) $ledger->previous_balance : 0;
            $p->new_balance = $ledger ? (float) $ledger->closing_balance : ($p->prev_balance - (float) $p->amount);
        }

        return view('admin_panel.customers.receipt_batch_print', compact(
            'payments',
            'batch_no',
            'first',
            'totalAmount',
            'amountInWords'
        ));
    }

    /**
     * Helper: Convert number to Pakistani Rupees Words
     */
    private function numberToWords($number)
    {
        $number = round($number, 2);
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }

        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "And " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paisa' : '';
        return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise . ' Only';
    }
}
