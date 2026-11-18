<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DoctorFundTransaction;
use App\Models\Zone;
use App\Models\Account;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DoctorFundController extends Controller
{
    /**
     * Display the main Doctor Investment & Zone Fund Ledger
     */
    public function index(Request $request)
    {
        $zones = Zone::orderBy('zone', 'asc')->get();
        $doctorsQuery = Customer::query();
        if ($request->filled('zone_id')) {
            $doctorsQuery->where('zone', $request->zone_id);
        }
        $doctors = $doctorsQuery->orderBy('customer_name', 'asc')->get();

        // Query transactions
        $query = DoctorFundTransaction::with(['zone', 'customer', 'creator'])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('type') && in_array($request->type, ['receiving', 'investment'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('narration', 'like', "%{$search}%")
                  ->orWhere('bank_account_name', 'like', "%{$search}%")
                  ->orWhere('purpose_tag', 'like', "%{$search}%");
            });
        }

        $allTransactions = $query->get();

        // Calculate Running Balance across the selected dataset
        $runningBalance = 0;
        $ledgerEntries = $allTransactions->map(function ($tx) use (&$runningBalance) {
            if ($tx->type === 'receiving') {
                $runningBalance += (float) $tx->amount;
                $tx->inflow = (float) $tx->amount;
                $tx->outflow = 0;
            } else {
                $runningBalance -= (float) $tx->amount;
                $tx->inflow = 0;
                $tx->outflow = (float) $tx->amount;
            }
            $tx->running_balance = $runningBalance;
            return $tx;
        });

        // Top KPI Cards Calculation
        $totalInflow = $allTransactions->where('type', 'receiving')->sum('amount');
        $totalOutflow = $allTransactions->where('type', 'investment')->sum('amount');
        $netBalance = $totalInflow - $totalOutflow;
        $totalDoctorsCount = $allTransactions->where('type', 'investment')->pluck('customer_id')->filter()->unique()->count();
        $totalZonesCount = $allTransactions->pluck('zone_id')->filter()->unique()->count();

        // Zone-wise Summary
        $zoneSummaries = DoctorFundTransaction::with('zone')
            ->select(
                'zone_id',
                DB::raw("SUM(CASE WHEN type = 'receiving' THEN amount ELSE 0 END) as total_received"),
                DB::raw("SUM(CASE WHEN type = 'investment' THEN amount ELSE 0 END) as total_invested"),
                DB::raw("COUNT(DISTINCT customer_id) as doctors_count"),
                DB::raw("COUNT(id) as total_entries")
            )
            ->groupBy('zone_id')
            ->get()
            ->map(function ($item) {
                $item->net_balance = (float) $item->total_received - (float) $item->total_invested;
                return $item;
            });

        // Bank Accounts list for optional selection
        $bankAccounts = Account::orderBy('title', 'asc')->get();

        return view('admin_panel.doctor_funds.index', compact(
            'ledgerEntries',
            'zones',
            'doctors',
            'bankAccounts',
            'totalInflow',
            'totalOutflow',
            'netBalance',
            'totalDoctorsCount',
            'totalZonesCount',
            'zoneSummaries'
        ));
    }

    /**
     * Show dedicated Full Page for Fund Receiving Voucher
     */
    public function createReceiving()
    {
        $prefix = 'DF-REC-' . date('Ym') . '-';
        $lastTx = DoctorFundTransaction::where('voucher_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();
        $nextSeq = 1;
        if ($lastTx && preg_match('/(\d+)$/', $lastTx->voucher_no, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }
        $nextVoucherNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        $zones = Zone::orderBy('zone', 'asc')->get();
        $doctors = Customer::orderBy('customer_name', 'asc')->get();
        $bankAccounts = Account::orderBy('title', 'asc')->get();

        // Load saved fund account name setting
        $fundAccountName = \App\Models\Setting::get('doctor_fund_account_name', 'Doctor Promotion Fund Account');

        // Recent receiving vouchers for history table
        $recentReceivings = DoctorFundTransaction::with(['zone', 'customer'])
            ->where('type', 'receiving')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        // Total fund balance in shadow account
        $totalReceived = DoctorFundTransaction::where('type', 'receiving')->sum('amount');
        $totalInvested = DoctorFundTransaction::where('type', 'investment')->sum('amount');
        $currentFundBalance = $totalReceived - $totalInvested;

        return view('admin_panel.doctor_funds.create_receiving', compact(
            'nextVoucherNo',
            'zones',
            'doctors',
            'bankAccounts',
            'recentReceivings',
            'currentFundBalance',
            'fundAccountName'
        ));
    }

    /**
     * Store a Receiving Transaction (Inflow from Distributor / Zone)
     */
    public function storeReceiving(Request $request)
    {
        $request->validate([
            'transaction_date'  => 'required|date',
            'zone_id'           => 'required|exists:zones,id',
            'customer_id'       => 'nullable|exists:customers,id',
            'amount'            => 'required|numeric|min:1',
            'bank_account_name' => 'nullable|string|max:190',
            'payment_method'    => 'required|string',
            'reference_no'      => 'nullable|string|max:100',
            'purpose_tag'       => 'nullable|string|max:100',
            'narration'         => 'nullable|string|max:1000',
            'attachment'        => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $prefix = 'DF-REC-' . date('Ym', strtotime($request->transaction_date)) . '-';
            $lastTx = DoctorFundTransaction::where('voucher_no', 'like', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();
            $nextSeq = 1;
            if ($lastTx && preg_match('/(\d+)$/', $lastTx->voucher_no, $matches)) {
                $nextSeq = intval($matches[1]) + 1;
            }
            $voucherNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            // Handle file upload
            $attachmentPath = null;
            $attachmentName = null;
            $attachmentMime = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                if ($file->isValid()) {
                    $attachmentName = $file->getClientOriginalName();
                    $attachmentMime = $file->getClientMimeType();
                    $attachmentPath = $file->store('doctor_funds/' . date('Y/m'), 'public');
                }
            }

            DoctorFundTransaction::create([
                'voucher_no'        => $voucherNo,
                'type'              => 'receiving',
                'account_id'        => $request->account_id,
                'bank_account_name' => $request->bank_account_name ?: 'Doctor Promotion Fund Account',
                'zone_id'           => $request->zone_id,
                'customer_id'       => $request->customer_id,
                'amount'            => $request->amount,
                'transaction_date'  => $request->transaction_date,
                'payment_method'    => $request->payment_method,
                'reference_no'      => $request->reference_no,
                'purpose_tag'       => $request->purpose_tag ?: 'Distributor Sales Collection',
                'attachment'        => $attachmentPath,
                'attachment_name'   => $attachmentName,
                'attachment_mime'   => $attachmentMime,
                'narration'         => $request->narration,
                'created_by'        => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('doctor-funds.index')
                ->with('success', "Fund Receiving Voucher [{$voucherNo}] recorded successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Doctor Fund Receiving Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error saving receiving voucher: ' . $e->getMessage());
        }
    }

    /**
     * Show dedicated Full Page for Doctor Investment Voucher
     */
    public function createInvestment()
    {
        $prefix = 'DF-INV-' . date('Ym') . '-';
        $lastTx = DoctorFundTransaction::where('voucher_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();
        $nextSeq = 1;
        if ($lastTx && preg_match('/(\d+)$/', $lastTx->voucher_no, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        }
        $nextVoucherNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

        $zones = Zone::orderBy('zone', 'asc')->get();
        $doctors = Customer::orderBy('customer_name', 'asc')->get();
        $bankAccounts = Account::orderBy('title', 'asc')->get();

        // Load saved fund account name setting
        $fundAccountName = \App\Models\Setting::get('doctor_fund_account_name', 'Doctor Promotion Fund Account');

        // Recent investment vouchers for history table
        $recentInvestments = DoctorFundTransaction::with(['zone', 'customer'])
            ->where('type', 'investment')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        // Total fund balance in shadow account
        $totalReceived = DoctorFundTransaction::where('type', 'receiving')->sum('amount');
        $totalInvested = DoctorFundTransaction::where('type', 'investment')->sum('amount');
        $currentFundBalance = $totalReceived - $totalInvested;

        return view('admin_panel.doctor_funds.create_investment', compact(
            'nextVoucherNo',
            'zones',
            'doctors',
            'bankAccounts',
            'recentInvestments',
            'currentFundBalance',
            'fundAccountName'
        ));
    }

    /**
     * Store an Investment Transaction (Outflow on Doctor)
     */
    public function storeInvestment(Request $request)
    {
        $request->validate([
            'transaction_date'  => 'required|date',
            'zone_id'           => 'required|exists:zones,id',
            'customer_id'       => 'required|exists:customers,id',
            'amount'            => 'required|numeric|min:1',
            'bank_account_name' => 'nullable|string|max:190',
            'payment_method'    => 'required|string',
            'reference_no'      => 'nullable|string|max:100',
            'purpose_tag'       => 'required|string|max:100',
            'narration'         => 'nullable|string|max:1000',
            'attachment'        => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $prefix = 'DF-INV-' . date('Ym', strtotime($request->transaction_date)) . '-';
            $lastTx = DoctorFundTransaction::where('voucher_no', 'like', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();
            $nextSeq = 1;
            if ($lastTx && preg_match('/(\d+)$/', $lastTx->voucher_no, $matches)) {
                $nextSeq = intval($matches[1]) + 1;
            }
            $voucherNo = $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            // Handle file upload
            $attachmentPath = null;
            $attachmentName = null;
            $attachmentMime = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                if ($file->isValid()) {
                    $attachmentName = $file->getClientOriginalName();
                    $attachmentMime = $file->getClientMimeType();
                    $attachmentPath = $file->store('doctor_funds/' . date('Y/m'), 'public');
                }
            }

            DoctorFundTransaction::create([
                'voucher_no'        => $voucherNo,
                'type'              => 'investment',
                'account_id'        => $request->account_id,
                'bank_account_name' => $request->bank_account_name ?: 'Doctor Promotion Fund Account',
                'zone_id'           => $request->zone_id,
                'customer_id'       => $request->customer_id,
                'amount'            => $request->amount,
                'transaction_date'  => $request->transaction_date,
                'payment_method'    => $request->payment_method,
                'reference_no'      => $request->reference_no,
                'purpose_tag'       => $request->purpose_tag,
                'attachment'        => $attachmentPath,
                'attachment_name'   => $attachmentName,
                'attachment_mime'   => $attachmentMime,
                'narration'         => $request->narration,
                'created_by'        => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('doctor-funds.index')
                ->with('success', "Doctor Investment Voucher [{$voucherNo}] recorded successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Doctor Fund Investment Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error saving investment voucher: ' . $e->getMessage());
        }
    }

    /**
     * Get single transaction details for viewing / attachment inspection
     */
    public function show($id)
    {
        $tx = DoctorFundTransaction::with(['zone', 'customer', 'creator'])->findOrFail($id);

        return response()->json([
            'id'                => $tx->id,
            'voucher_no'        => $tx->voucher_no,
            'type'              => $tx->type,
            'zone'              => $tx->zone ? $tx->zone->zone : 'N/A',
            'customer'          => $tx->customer ? $tx->customer->customer_name : 'General / Multiple',
            'amount'            => number_format((float) $tx->amount, 2),
            'amount_raw'        => (float) $tx->amount,
            'transaction_date'  => $tx->transaction_date->format('d M, Y'),
            'bank_account_name' => $tx->bank_account_name,
            'payment_method'    => $tx->payment_method,
            'reference_no'      => $tx->reference_no ?: '-',
            'purpose_tag'       => $tx->purpose_tag ?: '-',
            'narration'         => $tx->narration ?: '-',
            'created_by'        => $tx->creator ? $tx->creator->name : 'System',
            'attachment_url'    => $tx->attachment_url,
            'attachment_name'   => $tx->attachment_name,
            'is_pdf'            => $tx->is_pdf,
            'is_image'          => $tx->is_image,
        ]);
    }

    /**
     * AJAX endpoint: Get doctors by zone with their net outstanding balances
     * (Raw Ledger Sales Balance - Already Received in Doctor Fund)
     */
    public function getDoctorsByZone($zoneId)
    {
        $balanceService = app(BalanceService::class);

        // Pre-fetch all receivings grouped by customer_id for this zone
        $fundReceivingsByCustomer = DoctorFundTransaction::where('type', 'receiving')
            ->where('zone_id', $zoneId)
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->select('customer_id', DB::raw('SUM(amount) as total_received'))
            ->pluck('total_received', 'customer_id')
            ->toArray();

        $doctors = Customer::where('zone', $zoneId)
            ->select('id', 'customer_name', 'mobile')
            ->orderBy('customer_name', 'asc')
            ->get()
            ->map(function ($doc) use ($balanceService, $fundReceivingsByCustomer) {
                $rawBal = (float) $balanceService->getCustomerBalance($doc->id);
                $alreadyReceived = isset($fundReceivingsByCustomer[$doc->id]) ? (float) $fundReceivingsByCustomer[$doc->id] : 0.0;
                $netBal = $rawBal - $alreadyReceived;

                $lbl = $netBal >= 0 ? 'Dr' : 'Cr';
                $balFormatted = number_format(abs($netBal), 2) . ' ' . $lbl;
                
                return [
                    'id'               => $doc->id,
                    'customer_name'    => $doc->customer_name,
                    'mobile'           => $doc->mobile,
                    'raw_balance'      => $rawBal,
                    'already_received' => $alreadyReceived,
                    'already_received_formatted' => number_format($alreadyReceived, 2),
                    'raw_formatted'    => number_format(abs($rawBal), 2) . ' ' . ($rawBal >= 0 ? 'Dr' : 'Cr'),
                    'balance'          => $netBal,
                    'balance_label'    => $lbl,
                    'balance_formatted'=> $balFormatted,
                    'text'             => $doc->customer_name . " (Bal: {$balFormatted})"
                ];
            });

        // Zone total net balance (Sum of all customer balances in zone minus zone fund receivings)
        $zoneTotalRaw = (float) $doctors->sum('raw_balance');
        $zoneTotalFundReceived = (float) DoctorFundTransaction::where('type', 'receiving')
            ->where('zone_id', $zoneId)
            ->sum('amount');
        $zoneNetBal = $zoneTotalRaw - $zoneTotalFundReceived;

        return response()->json([
            'doctors'            => $doctors,
            'zone_total_balance' => (float) $zoneNetBal,
            'zone_total_formatted' => number_format(abs($zoneNetBal), 2) . ' ' . ($zoneNetBal >= 0 ? 'Dr' : 'Cr')
        ]);
    }

    /**
     * AJAX endpoint: Get single customer net outstanding balance
     */
    public function getCustomerBalance($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $balanceService = app(BalanceService::class);
        $rawBal = (float) $balanceService->getCustomerBalance($customer->id);
        
        // Sum of all fund receivings already recorded for this doctor
        $alreadyReceived = (float) DoctorFundTransaction::where('type', 'receiving')
            ->where('customer_id', $customer->id)
            ->sum('amount');

        $netBal = $rawBal - $alreadyReceived;
        $lbl = $netBal >= 0 ? 'Dr' : 'Cr';

        return response()->json([
            'id'                => $customer->id,
            'customer_name'     => $customer->customer_name,
            'mobile'            => $customer->mobile,
            'raw_balance'       => $rawBal,
            'raw_formatted'     => number_format(abs($rawBal), 2) . ' ' . ($rawBal >= 0 ? 'Dr' : 'Cr'),
            'already_received'  => $alreadyReceived,
            'already_received_formatted' => number_format($alreadyReceived, 2),
            'balance'           => $netBal,
            'balance_label'     => $lbl,
            'balance_formatted' => number_format(abs($netBal), 2) . ' ' . $lbl,
        ]);
    }

    /**
     * Save Doctor Fund account settings
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'doctor_fund_account_name' => 'required|string|max:200',
        ]);

        \App\Models\Setting::where('key', 'doctor_fund_account_name')
            ->update(['value' => $request->doctor_fund_account_name]);

        Cache::forget('setting_doctor_fund_account_name');

        return redirect()->back()->with('success', 'Doctor Fund account name updated successfully!');
    }

    /**
     * Delete a transaction record
     */
    public function destroy($id)
    {
        try {
            $tx = DoctorFundTransaction::findOrFail($id);

            // Delete attachment if exists
            if ($tx->attachment && Storage::disk('public')->exists($tx->attachment)) {
                Storage::disk('public')->delete($tx->attachment);
            }

            $voucherNo = $tx->voucher_no;
            $tx->delete();

            return redirect()->route('doctor-funds.index')
                ->with('success', "Transaction [{$voucherNo}] deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete transaction: ' . $e->getMessage());
        }
    }
}
