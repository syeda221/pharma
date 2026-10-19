<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountHead;
use App\Models\ExpenseCategory;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\ExpenseVoucher;
use App\Models\Narration;
use App\Models\PaymentVoucher;
use App\Models\ReceiptsVoucher;
use App\Models\VendorLedger;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function index($type)
    {

        // Sirf selected type ka data laa lo
        $vouchers = Voucher::where('voucher_type', $type)->latest()->get();
        $narration = Narration::where('expense_head', $type)->get();

        return view('admin_panel.accounts.expenses', [
            'vouchers' => $vouchers,
            'type' => $type,
            'narration' => $narration,
        ]);
    }

    public function store(Request $request)
    {
        // Validate that arrays are present and match in length
        $request->validate([
            'date' => 'required',
            'type' => 'required',
            'person' => 'required',
            'narration' => 'required',
            'amount' => 'required',
        ]);

        // Loop through each row and create a voucher
        foreach ($request->date as $index => $date) {
            Voucher::create([
                'voucher_type' => $request->sub_head,
                'sales_officer' => auth()->user()->name,
                'date' => $date,
                'type' => $request->type[$index],
                'person' => $request->person[$index],
                'sub_head' => $request->sub_head[$index] ?? null,
                'narration' => $request->narration[$index],
                'amount' => $request->amount[$index],
                'status' => 'draft',
            ]);
        }

        return back()->with('success', 'Vouchers saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Voucher $voucher)
    {
        //
    }

    public function receipt($id)
    {
        $voucher = Voucher::findOrFail($id);

        $customerName = $voucher->person; // Default
        $customerAddress = '-';
        $closingBalance = 0;

        // yahan accounts bhi show karwayn all heads
        // bank cash
        if ($voucher->type === 'Main Customer' && $voucher->mainCustomer) {
            $customerName = $voucher->mainCustomer->customer_name;
            $customerAddress = $voucher->mainCustomer->address;
            $closingBalance = $voucher->mainCustomer->closing_balance;
        } elseif ($voucher->type === 'Sub Customer' && $voucher->subCustomer) {
            $customerName = $voucher->subCustomer->customer_name;
            $customerAddress = $voucher->subCustomer->address;
            $closingBalance = $voucher->subCustomer->closing_balance;
        }

        return view('admin_panel.accounts.receipt', compact('voucher', 'customerName', 'customerAddress', 'closingBalance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Voucher $voucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        //
    }

    public function all_recepit_vochers()
    {
        // V2: Fetch Receipt Vouchers only
        $receipts = \App\Models\VoucherMaster::where('voucher_type', \App\Models\VoucherMaster::TYPE_RECEIPT)
            ->with('party') // Eager load the polymorphic party
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($receipts as $voucher) {
            $typeLabel = '-';
            $partyName = '-';

            if ($voucher->party) {
                // Determine Label from Class Name
                $class = get_class($voucher->party);
                if (str_contains($class, 'Customer')) {
                    $typeLabel = 'Customer';
                    $partyName = $voucher->party->customer_name ?? $voucher->party->name ?? '-';
                } elseif (str_contains($class, 'Vendor')) {
                    $typeLabel = 'Vendor';
                    $partyName = $voucher->party->name ?? '-';
                } elseif (str_contains($class, 'Account')) {
                    $typeLabel = 'Account';
                    $partyName = $voucher->party->title ?? '-';
                } else {
                    $typeLabel = class_basename($class);
                    $partyName = $voucher->party->name ?? '-';
                }
            }

            // Attach for View
            $voucher->type_label = $typeLabel;
            $voucher->party_name = $partyName;

            // Map old fields to new fields for View compatibility (or update View)
            // View uses: rvid, receipt_date, entry_date
            $voucher->rvid = $voucher->voucher_no;
            $voucher->receipt_date = $voucher->date->format('Y-m-d');
            $voucher->entry_date = $voucher->created_at->format('Y-m-d');

            // Fix: Map total_amount to amount for View compatibility
            if (! isset($voucher->amount)) {
                $voucher->amount = $voucher->total_amount;
            }
        }

        return view('admin_panel.vochers.all_recepit_vochers', compact('receipts'));
    }

    public function print($id)
    {
        \Log::info('Print Voucher Requested. ID: '.$id);

        // 1. Try V2 VoucherMaster of type receipt by ID
        $voucherV2 = \App\Models\VoucherMaster::where('id', $id)
            ->where('voucher_type', \App\Models\VoucherMaster::TYPE_RECEIPT)
            ->first();

        // 2. If not found by VoucherMaster ID, check if $id is from legacy ReceiptsVoucher or by RVID reference
        if (! $voucherV2) {
            $legacyRec = ReceiptsVoucher::find($id);
            if ($legacyRec) {
                $voucherV2 = \App\Models\VoucherMaster::where('voucher_type', \App\Models\VoucherMaster::TYPE_RECEIPT)
                    ->where(function($q) use ($legacyRec) {
                        $q->where('remarks', 'like', "%(Ref: {$legacyRec->rvid})%")
                          ->orWhere('remarks', 'like', "%#{$legacyRec->rvid}%")
                          ->orWhere('voucher_no', $legacyRec->rvid);
                    })
                    ->first();
            }
        }

        if ($voucherV2) {
            \Log::info('Found V2 Voucher: '.$voucherV2->voucher_no);

            // Lazy load relationships to avoid eager loading weirdness
            $voucherV2->load(['details.account', 'party']);

            $debitSum = (float) $voucherV2->details->where('debit', '>', 0)->sum('debit');
            $voucherAmount = $debitSum > 0 ? $debitSum : (float) ($voucherV2->total_amount ?: $voucherV2->amount);

            // -- Adapter for V2 to V1 View --
            $voucher = (object) [
                'rvid' => $voucherV2->voucher_no,
                'receipt_date' => $voucherV2->date ? $voucherV2->date->format('Y-m-d') : now()->format('Y-m-d'),
                'total_amount' => $voucherAmount,
                'remarks' => $voucherV2->remarks,
                'type' => 'unknown', // Default
            ];

            $rows = [];
            foreach ($voucherV2->details as $detail) {
                // Receipt Voucher: Only show DEBIT rows (Cash/Bank received into)
                // Skip Credit rows (Accounts Receivable / AR) — they are the accounting offset, not useful for print
                if ($voucherV2->voucher_type === 'receipt' && $detail->credit > 0) {
                    continue; // Skip Cr side (AR)
                }

                $accTitle  = $detail->account->title ?? '-';
                $accCode   = $detail->account->account_code ?? '-';
                $headName  = $detail->account->accountHead->name ?? '-';

                $rows[] = [
                    'narration'    => $detail->narration,
                    'reference'    => '-',
                    'account_head' => $headName,
                    'account_name' => $accTitle,
                    'account_code' => $accCode,
                    'amount'       => $detail->debit > 0 ? $detail->debit : $detail->credit,
                ];
            }

            // Party Logic
            $party = $voucherV2->party;
            $previousBalance = 0;

            if ($party) {
                if ($party instanceof \App\Models\Customer) {
                    $voucher->type = ($party->customer_type == 'Walking Customer') ? 'walkin' : 'customer';

                    $party->name = $party->customer_name;
                    $party->address = $party->address ?? '-';
                    $party->mobile = $party->mobile ?? '-';

                    // Find CustomerLedger entry associated with this voucher
                    $ledgerEntry = \App\Models\CustomerLedger::where('customer_id', $party->id)
                        ->where(function($q) use ($voucherV2) {
                            $q->where('description', 'like', "%{$voucherV2->voucher_no}%");
                            if (preg_match('/Ref:\s*([^)]+)/', $voucherV2->remarks, $matches)) {
                                $q->orWhere('description', 'like', "%{$matches[1]}%");
                            }
                        })
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($ledgerEntry) {
                        $previousBalance = (float) $ledgerEntry->previous_balance;
                    } else {
                        $previousBalance = (float) (\App\Models\CustomerLedger::where('customer_id', $party->id)
                            ->where('created_at', '<', $voucherV2->created_at)
                            ->orderBy('id', 'desc')
                            ->value('closing_balance') ?? ($party->opening_balance ?? 0));
                    }

                } elseif ($party instanceof \App\Models\Vendor) {
                    $voucher->type = 'vendor';
                    $party->address = $party->address ?? '-';
                    $party->phone = $party->phone ?? '-';

                    $ledgerEntry = \App\Models\VendorLedger::where('vendor_id', $party->id)
                        ->where(function($q) use ($voucherV2) {
                            $q->where('description', 'like', "%{$voucherV2->voucher_no}%");
                            if (preg_match('/Ref:\s*([^)]+)/', $voucherV2->remarks, $matches)) {
                                $q->orWhere('description', 'like', "%{$matches[1]}%");
                            }
                        })
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($ledgerEntry) {
                        $previousBalance = (float) $ledgerEntry->previous_balance;
                    } else {
                        $previousBalance = (float) (\App\Models\VendorLedger::where('vendor_id', $party->id)
                            ->where('created_at', '<', $voucherV2->created_at)
                            ->orderBy('id', 'desc')
                            ->value('closing_balance') ?? ($party->opening_balance ?? 0));
                    }

                } elseif ($party instanceof \App\Models\Account) {
                    $voucher->type = '1';
                    $party->name = $party->title;
                    $party->phone = $party->account_code;
                    $party->head_name = $party->accountHead->name ?? 'Account';

                    $previousBalance = (float) ($party->opening_balance ?? 0);
                }
            } else {
                $previousBalance = 0;
            }

            return view('admin_panel.vochers.print', compact('voucher', 'rows', 'party', 'previousBalance'));
        }

        // 2. Fallback to V1 Legacy (Original Code)
        $voucher = ReceiptsVoucher::findOrFail($id);

        // Decode JSON arrays
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // Rows build
        $rows = [];
        foreach ($narrations as $index => $narrId) {
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;
            $accountHead = DB::table('account_heads')->where('id', $accountHeads[$index] ?? null)->value('name');
            $account = DB::table('accounts')->where('id', $accounts[$index] ?? null)->first();
            $amount = (float) ($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        // 🧩 Party setup — dynamic based on type
        $party = null;
        $previousBalance = 0;

        // ✅ If type is numeric → means from Account Head
        if (is_numeric($voucher->type)) {
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object) [
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            // ✅ If vendor
        } elseif ($voucher->type === 'vendor') {
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ If customer
        } elseif ($voucher->type === 'customer') {
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ If walkin
        } elseif ($voucher->type === 'walkin') {
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        return view('admin_panel.vochers.print', compact('voucher', 'rows', 'party', 'previousBalance'));
    }

    public function getAccountsByHead($headId)
    {
        $accounts = Account::where('head_id', $headId)->where('status', 1)->get();

        // echo "<pre>";
        // print_r($accounts);
        // echo "</pre>";
        // dd();
        return response()->json($accounts);
    }

    public function getOpeningBalance($type, $id)
    {
        if ($type === 'customer' || $type === 'walkin') {
            $customer = Customer::find($id);

            // echo "<pre>";
            // print_r($customer);
            // echo "<pre>";
            // dd();
            return response()->json([
                'opening_balance' => $customer->opening_balance ?? 0,
            ]);
        }

        // Account case
        $account = AccountHead::find($id);

        return response()->json([
            'opening_balance' => $account->opening_balance ?? 0,
        ]);
    }

    public function recepit_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Receipts Voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::whereIn('name', ['Cash', 'bank', 'cash', 'Bank'])->get();

        // echo "<pre>";
        // print_r($AccountHeads) ;
        // echo "<pre>";
        // dd();

        // Last RVID nikalna
        $lastVoucher = \App\Models\ReceiptsVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'RVID-'.str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.reciepts_vouchers', compact('narrations', 'AccountHeads', 'nextRvid'));
    }

    public function store_rec_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            $rvid = $request->rvid ?: \App\Models\ReceiptsVoucher::generateRVID(auth()->id());
            $narrationIds = [];

            if ($request->narration_id && is_array($request->narration_id)) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    $manualType = $request->narration_type_text[$index] ?? 'Manual';

                    if (empty($narrId) && ! empty($manualText)) {
                        // Auto expense_head set based on voucher type
                        $expenseHead = 'Receipts Voucher';
                        if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                            $expenseHead = 'Receipts Voucher';
                        }

                        $new = \App\Models\Narration::create([
                            'expense_head' => $expenseHead,
                            'narration' => $manualText,
                        ]);

                        $narrationIds[] = (string) $new->id; // store as string → ["7"]
                    } else {
                        $narrationIds[] = (string) $narrId; // force string format
                    }
                }
            }

            $voucherData = [
                'rvid' => $rvid,
                'receipt_date' => $request->receipt_date,
                'entry_date' => $request->entry_date,
                'type' => $request->vendor_type,
                'party_id' => $request->vendor_id,
                'tel' => $request->tel,
                'remarks' => $request->remarks,

                'narration_id' => json_encode($narrationIds),
                'reference_no' => json_encode($request->reference_no),
                'row_account_head' => json_encode($request->row_account_head),
                'row_account_id' => json_encode($request->row_account_id),
                'discount_value' => json_encode($request->discount_value),
                // 'kg'               => json_encode($request->kg),
                'rate' => json_encode($request->rate),
                'amount' => json_encode($request->amount),
                'total_amount' => $request->total_amount,
                'processed' => true,
            ];

            $rec = ReceiptsVoucher::create($voucherData);
            // ✅ V2 VOUCHER INTEGRATION (Primary Logic Now)
            try {
                \Log::info('V2 Integration Start. Type: '.$request->vendor_type.', ID: '.$request->vendor_id);

                $vType = strtolower($request->vendor_type);
                $partyType = null;
                $creditAccountId = null;
                $balanceService = app(\App\Services\BalanceService::class);

                if ($vType == 'customer' || $vType == 'walkin') {
                    $partyType = \App\Models\Customer::class;
                    $creditAccountId = $balanceService->getAccountsReceivableId();
                } elseif ($vType == 'vendor') {
                    $partyType = \App\Models\Vendor::class;
                    $creditAccountId = $balanceService->getAccountsPayableId();
                } else {
                    $partyType = \App\Models\Account::class;
                    $creditAccountId = $request->vendor_id;
                }

                if ($creditAccountId) {
                    $v2Lines = [];
                    // DEBIT SIDE (Cash/Bank) - From Row Inputs
                    if ($request->row_account_id && $request->amount) {
                        foreach ($request->row_account_id as $idx => $accId) {
                            $amt = (float) ($request->amount[$idx] ?? 0);
                            if ($amt > 0) {
                                $v2Lines[] = [
                                    'account_id' => $accId,
                                    'debit' => $amt,
                                    'credit' => 0,
                                    'narration' => $request->narration_text[$idx] ?? 'Receipt',
                                ];
                            }
                        }
                    }

                    // CREDIT SIDE (Customer/AR) - Total Amount
                    $totalAmt = (float) $request->total_amount;
                    if ($totalAmt > 0) {
                        $v2Lines[] = [
                            'account_id' => $creditAccountId,
                            'debit' => 0,
                            'credit' => $totalAmt,
                            'narration' => 'Receipt from '.$request->vendor_type,
                        ];
                    }

                    $v2Master = null;
                    if (! empty($v2Lines)) {
                        $v2Master = app(\App\Services\VoucherService::class)->createVoucher([
                            'voucher_type' => 'receipt',
                            'date' => $request->receipt_date,
                            'status' => 'posted',
                            'party_type' => $partyType,
                            'party_id' => $request->vendor_id,
                            'remarks' => $request->remarks." (Ref: $rvid)",
                        ], $v2Lines, auth()->id());

                        \Log::info('V2 Voucher Created Successfully.');

                        // ✅ Also update CustomerLedger for correct balance display on form
                        if (($vType == 'customer' || $vType == 'walkin') && $totalAmt > 0) {
                            $latestLedger = CustomerLedger::where('customer_id', $request->vendor_id)->latest()->first();
                            $prevBal = $latestLedger ? $latestLedger->closing_balance : (
                                \App\Models\Customer::find($request->vendor_id)->opening_balance ?? 0
                            );
                            CustomerLedger::create([
                                'customer_id'      => $request->vendor_id,
                                'admin_or_user_id' => auth()->id(),
                                'previous_balance' => $prevBal,
                                'opening_balance'  => 0,
                                'closing_balance'  => $prevBal - $totalAmt, // Payment received → balance reduces
                                'description'      => 'Receipt Voucher '.$rvid,
                            ]);
                        }

                    } else {
                        \Log::warning('V2 Lines Empty. Total Amt: '.$totalAmt);
                    }
                } else {
                    \Log::warning("Credit Account ID missing for type: $vType");
                }
            } catch (\Exception $e) {
                \Log::error('V2 Sync Error: '.$e->getMessage());
                // Silently fail or return error message if preferred, but usually we log.
            }

            DB::commit();

            $targetPrintId = isset($v2Master) && $v2Master ? $v2Master->id : $rec->id;

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Receipt Voucher saved successfully!',
                    'voucher_id' => $targetPrintId,
                    'print_url' => route('print', $targetPrintId),
                    'all_vouchers_url' => route('all_recepit_vochers'),
                ]);
            }

            return redirect()->route('print', $targetPrintId)->with('success', 'Receipt Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function Payment_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Payment voucher')
            ->pluck('narration', 'id');
        $AccountHeads = AccountHead::whereIn('name', ['Cash', 'bank', 'cash', 'Bank'])->get();
        // echo"<pre>";
        // print_r($AccountHeads);
        // echo"</pre>";
        // dd();

        // Last RVID nikalna
        $lastVoucher = \App\Models\PaymentVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextPVID = 'PVID-'.str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.payment_vochers.payment_vouchers', compact('narrations', 'AccountHeads', 'nextPVID'));
    }

    public function store_Pay_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            $pvid = PaymentVoucher::generateInvoiceNo();
            $narrationIds = [];

            // Narration handling (assuming multiple narrations from table)
            if ($request->narration_id) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    if (empty($narrId) && ! empty($manualText)) {
                        $new = \App\Models\Narration::create([
                            'expense_head' => 'Payment voucher',
                            'narration' => $manualText,
                        ]);
                        $narrationIds[] = (string) $new->id;
                    } else {
                        $narrationIds[] = (string) $narrId;
                    }
                }
            }

            // In this new design:
            // Header = Source (Account) -> row_account_id (Single)
            // Table = Destination (Party) -> vendor_type[], vendor_id[] (Multiple)

            $voucherData = [
                'pvid' => $pvid,
                'receipt_date' => $request->receipt_date,
                'entry_date' => $request->entry_date,

                // Store Header Source as single values
                'row_account_head' => $request->header_account_head,
                'row_account_id' => $request->header_account_id,
                'remarks' => $request->remarks,

                // Store Table Destinations as JSON
                'type' => json_encode($request->vendor_type),
                'party_id' => json_encode($request->vendor_id),
                'narration_id' => json_encode($narrationIds),
                'reference_no' => json_encode($request->reference_no),
                'discount_value' => json_encode($request->discount_value),
                'rate' => json_encode($request->rate),
                'amount' => json_encode($request->amount),
                'total_amount' => $request->total_amount,
            ];

            $payment = PaymentVoucher::create($voucherData);

            $totalAmount = (float) $request->total_amount;

            // ✅ V2 VOUCHER INTEGRATION (Primary Logic for printing and GL)
            try {
                \Log::info('V2 Payment Integration Start. Header Account: '.$request->header_account_id);

                $balanceService = app(\App\Services\BalanceService::class);
                $firstRow = null;
                if ($request->vendor_id && count($request->vendor_id) > 0) {
                    $firstRow = [
                        'type' => $request->vendor_type[0] ?? null,
                        'id'   => $request->vendor_id[0] ?? null,
                    ];
                }

                $partyType = null;
                if ($firstRow) {
                    $fType = strtolower($firstRow['type']);
                    if ($fType == 'customer' || $fType == 'walkin') {
                        $partyType = \App\Models\Customer::class;
                    } elseif ($fType == 'vendor') {
                        $partyType = \App\Models\Vendor::class;
                    } else {
                        $partyType = \App\Models\Account::class;
                    }
                }

                $v2Lines = [];
                // 1. DEBIT SIDE (AP / AR / Accounts) - Table Destinations
                if ($request->vendor_id && $request->amount) {
                    foreach ($request->vendor_id as $idx => $partyId) {
                        $type = $request->vendor_type[$idx] ?? null;
                        $amt = (float) ($request->amount[$idx] ?? 0);
                        if ($amt <= 0) {
                            continue;
                        }

                        $controlAccountId = null;
                        if ($type == 'vendor') {
                            $controlAccountId = $balanceService->getAccountsPayableId();
                        } elseif ($type == 'customer' || $type == 'walkin') {
                            $controlAccountId = $balanceService->getAccountsReceivableId();
                        } else {
                            $controlAccountId = $partyId;
                        }

                        if ($controlAccountId) {
                            $v2Lines[] = [
                                'account_id' => $controlAccountId,
                                'debit' => $amt,
                                'credit' => 0,
                                'narration' => $request->narration_text[$idx] ?? "Payment to " . $type,
                            ];
                        }
                    }
                }

                // 2. CREDIT SIDE (Cash/Bank) - Header Source
                if ($totalAmount > 0 && $request->header_account_id) {
                    $v2Lines[] = [
                        'account_id' => $request->header_account_id,
                        'debit' => 0,
                        'credit' => $totalAmount,
                        'narration' => $request->remarks ?: "Payment from Cash/Bank",
                    ];
                }

                if (! empty($v2Lines)) {
                    app(\App\Services\VoucherService::class)->createVoucher([
                        'voucher_type' => 'payment',
                        'date' => $request->receipt_date,
                        'status' => 'posted',
                        'party_type' => $partyType,
                        'party_id' => $firstRow ? $firstRow['id'] : null,
                        'remarks' => $request->remarks." (Ref: $pvid)",
                    ], $v2Lines, auth()->id());

                    \Log::info('V2 Payment Voucher Created Successfully.');
                }
            } catch (\Exception $e) {
                \Log::error('V2 Payment Sync Error: '.$e->getMessage());
            }

            /**
             * STEP 2: Legacy Table Destinations (Parties) - Update polymorphic ledger balances (Closing balances)
             */
            if ($request->vendor_id && $request->amount) {
                foreach ($request->vendor_id as $index => $partyId) {
                    $type = $request->vendor_type[$index] ?? null;
                    $rowAmount = isset($request->amount[$index]) ? (float) $request->amount[$index] : 0;

                    if ($rowAmount <= 0) {
                        continue;
                    }

                    if ($type === 'vendor') {
                        $ledger = VendorLedger::where('vendor_id', $partyId)->latest()->first();
                        $bal = $ledger ? $ledger->closing_balance : 0;
                        VendorLedger::create([
                            'vendor_id'         => $partyId,
                            'admin_or_user_id'  => auth()->id(),
                            'opening_balance'   => 0,
                            'previous_balance'  => $bal,
                            'closing_balance'   => $bal - $rowAmount, // ✅ MINUS: payment reduces vendor balance
                        ]);

                    } elseif ($type === 'customer' || $type === 'walkin') {
                        $ledger = CustomerLedger::where('customer_id', $partyId)->latest()->first();
                        $bal = $ledger ? $ledger->closing_balance : 0;
                        CustomerLedger::create([
                            'customer_id'      => $partyId,
                            'admin_or_user_id' => auth()->id(),
                            'previous_balance' => $bal,
                            'opening_balance'  => 0,
                            'closing_balance'  => $bal + $rowAmount, // ✅ PLUS: paying customer increases customer balance
                        ]);
                    } elseif ($type) {
                        // Account ID in table
                        $acc = Account::find($partyId);
                        if ($acc) {
                            $acc->current_balance = $acc->current_balance + $rowAmount;
                            $acc->save();
                        }
                    }
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment Voucher saved successfully!',
                    'voucher_id' => $payment->id,
                    'print_url' => route('Paymentprint', $payment->id),
                    'all_vouchers_url' => route('all_Payment_vochers'),
                ]);
            }

            return redirect()->route('all_Payment_vochers')->with('success', 'Payment Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function all_Payment_vochers()
    {
        // V2: Fetch Payment Vouchers only
        $receipts = \App\Models\VoucherMaster::where('voucher_type', \App\Models\VoucherMaster::TYPE_PAYMENT)
            ->with('party') // Eager load the polymorphic party
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($receipts as $voucher) {
            $typeLabel = '-';
            $partyName = '-';

            if ($voucher->party) {
                // Determine Label from Class Name
                $class = get_class($voucher->party);
                if (str_contains($class, 'Customer')) {
                    $typeLabel = 'Customer';
                    $partyName = $voucher->party->customer_name ?? $voucher->party->name ?? '-';
                } elseif (str_contains($class, 'Vendor')) {
                    $typeLabel = 'Vendor';
                    $partyName = $voucher->party->name ?? '-';
                } elseif (str_contains($class, 'Account')) {
                    $typeLabel = 'Account';
                    $partyName = $voucher->party->title ?? '-';
                } else {
                    $typeLabel = class_basename($class);
                    $partyName = $voucher->party->name ?? '-';
                }
            }

            // Attach for View
            $voucher->type_label = $typeLabel;
            $voucher->party_name = $partyName;

            // Map fields for View compatibility
            $voucher->pvid = $voucher->voucher_no;
            $voucher->receipt_date = $voucher->date->format('Y-m-d');
            $voucher->entry_date = $voucher->created_at->format('Y-m-d');

            // Fix for view expecting 'amount' field
            if (! isset($voucher->amount)) {
                $voucher->amount = $voucher->total_amount;
            }
        }

        return view('admin_panel.vochers.payment_vochers.all_payment_vochers', compact('receipts'));
    }

    public function Paymentprint($id)
    {
        // 1. Try V2 VoucherMaster First
        $voucherV2 = \App\Models\VoucherMaster::find($id);

        if ($voucherV2) {
            // Lazy load relationships
            $voucherV2->load(['details.account', 'party']);

            // -- Adapter for V2 to V1 View --
            $voucher = (object) [
                'pvid' => $voucherV2->voucher_no,
                'receipt_date' => $voucherV2->date->format('Y-m-d'),
                'total_amount' => $voucherV2->amount,
                'remarks' => $voucherV2->remarks,
                'type' => 'unknown', // Default
            ];

            if (! isset($voucher->total_amount)) {
                $voucher->total_amount = $voucherV2->total_amount;
            }

            $rows = [];
            foreach ($voucherV2->details as $detail) {
                // Payment Voucher: Only show CREDIT rows (which account money left from - Cash/Bank)
                // Skip Debit rows (AP/Payable offset) — not useful for print
                if ($voucherV2->voucher_type === 'payment' && $detail->debit > 0) {
                    continue; // Skip Dr side (AP)
                }

                $headName = $detail->account->accountHead->name ?? '-';
                $accName  = $detail->account->title ?? '-';
                $accCode  = $detail->account->account_code ?? '-';

                $rows[] = [
                    'narration'    => $detail->narration,
                    'reference'    => '-',
                    'account_head' => $headName,
                    'account_name' => $accName,
                    'account_code' => $accCode,
                    'amount'       => $detail->credit > 0 ? $detail->credit : $detail->debit,
                ];
            }

            // Party Logic
            $party = $voucherV2->party;
            $previousBalance = 0;

            if ($party) {
                if ($party instanceof \App\Models\Customer) {
                    $voucher->type = ($party->customer_type == 'Walking Customer') ? 'walkin' : 'customer';

                    $party->name = $party->customer_name;
                    $party->address = $party->address ?? '-';
                    $party->mobile = $party->mobile ?? '-'; // View uses mobile/phone? View uses mobile for customer

                    $previousBalance = \App\Models\CustomerLedger::where('customer_id', $party->id)
                        ->where('created_at', '<', $voucherV2->created_at)
                        ->orderBy('id', 'desc')
                        ->value('closing_balance') ?? ($party->opening_balance ?? 0);

                } elseif ($party instanceof \App\Models\Vendor) {
                    $voucher->type = 'vendor';
                    $party->address = $party->address ?? '-';
                    $party->phone = $party->phone ?? '-'; // View uses phone

                    $previousBalance = \App\Models\VendorLedger::where('vendor_id', $party->id)
                        ->where('created_at', '<', $voucherV2->created_at)
                        ->orderBy('id', 'desc')
                        ->value('closing_balance') ?? ($party->opening_balance ?? 0);

                } elseif ($party instanceof \App\Models\Account) {
                    $voucher->type = '1'; // Numeric triggers Account Block
                    $party->name = $party->title;
                    $party->phone = $party->account_code; // View uses phone
                    $party->head_name = $party->accountHead->name ?? 'Account';

                    $previousBalance = $party->opening_balance;
                }
            }

            return view('admin_panel.vochers.payment_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance'));
        }

        $voucher = \App\Models\PaymentVoucher::findOrFail($id);

        // Decode JSON arrays
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // 🧾 Build detailed rows
        $rows = [];
        foreach ($narrations as $index => $narrId) {
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;
            $accountHead = DB::table('account_heads')->where('id', $accountHeads[$index] ?? null)->value('name');
            $account = DB::table('accounts')->where('id', $accounts[$index] ?? null)->first();
            $amount = (float) ($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $account->title ?? null,
                'account_code' => $account->account_code ?? null,
                'amount' => $amount,
            ];
        }

        // 🧩 Party setup — dynamic based on type
        $party = null;
        $previousBalance = 0;

        // ✅ Account Head type (numeric)
        if (is_numeric($voucher->type)) {
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object) [
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            $previousBalance = $account->opening_balance ?? 0;

            // ✅ Vendor
        } elseif ($voucher->type === 'vendor') {
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ Customer
        } elseif ($voucher->type === 'customer') {
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;

            // ✅ Walking customer
        } elseif ($voucher->type === 'walkin') {
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        return view('admin_panel.vochers.payment_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance'));
    }

    public function partyList(Request $request)
    {
        $type = $request->type ?? 'customer';
        $search = $request->search ?? $request->q ?? '';
        $data = [];

        try {
            $balanceService = app(\App\Services\BalanceService::class);

            if ($type == 'vendor') {
                $query = \Illuminate\Support\Facades\DB::table('vendors')
                    ->select('id', 'name', 'phone as mobile', 'address', 'opening_balance');

                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
                }

                $vendors = $query->orderBy('name')->get();
                foreach ($vendors as $vendor) {
                    $vendor->closing_balance = $balanceService->getVendorBalance($vendor->id);
                    $bal = number_format(abs($vendor->closing_balance), 0);
                    $lbl = $vendor->closing_balance >= 0 ? 'Cr' : 'Dr';
                    $vendor->customer_name = $vendor->name;
                    $vendor->text = $vendor->name . " (Bal: {$bal} {$lbl})";
                    $data[] = $vendor;
                }
            } elseif ($type == 'customer' || $type == 'walkin') {
                $query = \App\Models\Customer::query();

                if ($type == 'walkin') {
                    $query->where('customer_type', 'Walking Customer');
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('customer_name', 'like', "%{$search}%")
                          ->orWhere('customer_id',   'like', "%{$search}%")
                          ->orWhere('mobile',        'like', "%{$search}%");
                    });
                }

                $customers = $query->orderBy('customer_name')
                    ->get(['id', 'customer_id', 'customer_name', 'mobile', 'address', 'status', 'opening_balance']);

                foreach ($customers as $customer) {
                    $customer->closing_balance = $balanceService->getCustomerBalance($customer->id);
                    $bal = number_format(abs($customer->closing_balance), 0);
                    $lbl = $customer->closing_balance >= 0 ? 'Dr' : 'Cr';
                    
                    $customer->text = ($customer->customer_id ? $customer->customer_id . ' — ' : '') . $customer->customer_name . " (Bal: {$bal} {$lbl})";
                    $customer->name = $customer->customer_name;
                    $customer->remarks = $customer->status;
                    $data[] = $customer;
                }
            } elseif (is_numeric($type)) {
                $query = \App\Models\Account::where('account_head_id', $type);
                if ($search) {
                    $query->where(function($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                          ->orWhere('account_code', 'like', "%{$search}%");
                    });
                }
                $accounts = $query->orderBy('title')->get();
                foreach ($accounts as $acc) {
                    $acc->closing_balance = $acc->current_balance ?? 0;
                    $acc->mobile = $acc->account_code;
                    $acc->name = $acc->title;
                    $acc->customer_name = $acc->title;
                    $acc->text = $acc->title . " [{$acc->account_code}]";
                    $data[] = $acc;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Party List Error: '.$e->getMessage());

            return response()->json([]);
        }

        return response()->json($data);
    }

    public function expense_vochers()
    {
        $narrations = \App\Models\Narration::where('expense_head', 'Expense voucher')
            ->pluck('narration', 'id');
        $expenseCategories = \App\Models\ExpenseCategory::orderBy('name')->get();
        $AccountHeads = AccountHead::whereIn('name', ['Cash', 'bank', 'cash', 'Bank'])->get();

        // Last RVID nikalna
        $lastVoucher = \App\Models\ExpenseVoucher::latest('id')->first();

        // Next ID generate karna
        $nextId = $lastVoucher ? $lastVoucher->id + 1 : 1;
        $nextRvid = 'EVID-'.str_pad($nextId, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.expense_vochers.expense_vouchers', compact('narrations', 'expenseCategories', 'AccountHeads', 'nextRvid'));
    }

    public function store_expense_vochers(Request $request)
    {
        DB::beginTransaction();
        try {
            $evid = ExpenseVoucher::generateInvoiceNo();
            $narrationIds = [];

            if ($request->narration_id && is_array($request->narration_id)) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;
                    $manualType = $request->narration_type_text[$index] ?? 'Manual';

                    if (empty($narrId) && ! empty($manualText)) {
                        // Auto expense_head set based on voucher type
                        $expenseHead = 'Expense voucher';
                        if (stripos($manualType, 'Receipt') !== false || $request->voucher_type == 'receipt') {
                            $expenseHead = 'Expense voucher';
                        }

                        $new = \App\Models\Narration::create([
                            'expense_head' => $expenseHead,
                            'narration' => $manualText,
                        ]);

                        $narrationIds[] = (string) $new->id; // store as string → ["7"]
                    } else {
                        $narrationIds[] = (string) $narrId; // force string format
                    }
                }
            }
            $voucherData = [
                'evid' => $evid,
                'entry_date' => $request->entry_date,
                'type' => $request->vendor_type,
                'party_id' => $request->vendor_id,
                'tel' => $request->tel,
                'remarks' => $request->remarks,
                'reference_no' => $request->ref_no_header,
                'narration_id' => json_encode($narrationIds),
                'row_account_head' => json_encode($request->row_account_head ?? array_fill(0, count($request->row_account_id ?? []), "0")),
                'row_account_id' => json_encode($request->row_account_id),
                'amount' => json_encode($request->amount),
                'total_amount' => $request->total_amount,
            ];

            $expense = ExpenseVoucher::create($voucherData);

            $amount = (float) $request->total_amount;

            $journalService = app(\App\Services\JournalEntryService::class);
            $balanceService = app(\App\Services\BalanceService::class);

            /**
             * STEP 1: Expense Accounts (row side) → PLUS (Debit)
             */
            // Find or create "Expense" Account Head
            $expenseHead = AccountHead::firstOrCreate(
                ['name' => 'Expense'],
                ['opening_balance' => 0]
            );

            // Find or create "General Expense" Account
            $generalExpenseAccount = Account::firstOrCreate(
                ['account_code' => 'GEN-EXP'],
                [
                    'head_id' => $expenseHead->id,
                    'title' => 'General Expense',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'type' => 'Debit',
                    'status' => 1
                ]
            );

            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = isset($request->amount[$index]) ? (float) $request->amount[$index] : 0;

                    if ($rowAmount > 0) {
                        $categoryName = DB::table('expense_categories')->where('id', $accId)->value('name') ?? 'General Expense';
                        
                        $journalService->recordEntry(
                            $expense,
                            $generalExpenseAccount->id,
                            $rowAmount, // Debit Expense
                            0, // Credit
                            "Expense Voucher #$evid ($categoryName)",
                            $request->entry_date ?? date('Y-m-d')
                        );
                    }
                }
            }

            /**
             * STEP 2: Party side → MINUS
             */
            if ($request->vendor_type === 'vendor') {
                $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance = $ledger->closing_balance - $amount; // MINUS
                    $ledger->save();
                } else {
                    VendorLedger::create([
                        'vendor_id' => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'opening_balance' => 0,
                        'previous_balance' => 0,
                        'closing_balance' => -$amount,
                    ]);
                }

                // Credit Vendor Liability Side
                $journalService->recordEntry(
                    $expense,
                    $balanceService->getAccountsPayableId(),
                    0, // Debit
                    $amount, // Credit vendor liability
                    "Expense Voucher #$evid",
                    $request->entry_date ?? date('Y-m-d'),
                    \App\Models\Vendor::find($request->vendor_id)
                );
            } elseif ($request->vendor_type === 'customer') {
                $ledger = CustomerLedger::where('customer_id', $request->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance = $ledger->closing_balance - $amount; // MINUS
                    $ledger->save();
                } else {
                    CustomerLedger::create([
                        'customer_id' => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => 0,
                        'opening_balance' => 0,
                        'closing_balance' => -$amount,
                    ]);
                }

                // Credit Customer Receivable Side
                $journalService->recordEntry(
                    $expense,
                    $balanceService->getAccountsReceivableId(),
                    0, // Debit
                    $amount, // Credit Accounts Receivable
                    "Expense Voucher #$evid",
                    $request->entry_date ?? date('Y-m-d'),
                    \App\Models\Customer::find($request->vendor_id)
                );
            } else {
                // yahan vendor_type numeric (1,2,3) hai → matlab Account ID
                $account = Account::find($request->vendor_id);
                if ($account) {
                    // Credit Cash/Bank Side
                    $journalService->recordEntry(
                        $expense,
                        $account->id,
                        0, // Debit
                        $amount, // Credit Cash/Bank
                        "Expense Voucher #$evid",
                        $request->entry_date ?? date('Y-m-d')
                    );
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Expense Voucher saved successfully!',
                    'voucher_id' => $expense->id,
                    'print_url' => route('expenseprint', $expense->id),
                    'all_vouchers_url' => route('all_expense_vochers'),
                ]);
            }

            return redirect()->route('all_expense_vochers')->with('success', 'Expense Voucher saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function all_expense_vochers()
    {
        $receipts = \App\Models\ExpenseVoucher::orderBy('id', 'DESC')->get();

        foreach ($receipts as $voucher) {
            $partyName = '-';
            $typeLabel = '-';

            // 🧩 If type is numeric → Account Head / Account
            if (is_numeric($voucher->type)) {
                $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
                $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

                $typeLabel = $accountHead->name ?? 'Account';
                $partyName = $account->title ?? '-';
            } elseif ($voucher->type === 'vendor') {
                $vendor = DB::table('vendors')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Vendor';
                $partyName = $vendor->name ?? '-';
            } elseif ($voucher->type === 'customer') {
                $customer = DB::table('customers')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Customer';
                $partyName = $customer->customer_name ?? '-';
            } elseif ($voucher->type === 'walkin') {
                $walkin = DB::table('customers')
                    ->where('id', $voucher->party_id)
                    ->where('customer_type', 'Walking Customer')
                    ->first();
                $typeLabel = 'Walk-in';
                $partyName = $walkin->customer_name ?? '-';
            } else {
                $account = DB::table('accounts')->where('id', $voucher->party_id)->first();
                $typeLabel = 'Account';
                $partyName = $account->title ?? '-';
            }

            // 🔗 Attach extra fields for Blade
            $voucher->type_label = $typeLabel;
            $voucher->party_name = $partyName;
        }

        return view('admin_panel.vochers.expense_vochers.all_expense_vochers', compact('receipts'));
    }

    public function edit_expense_vochers($id)
    {
        $voucher = ExpenseVoucher::findOrFail($id);
        $narrations = \App\Models\Narration::where('expense_head', 'Expense voucher')
            ->pluck('narration', 'id');
        $expenseCategories = \App\Models\ExpenseCategory::orderBy('name')->get();
        $AccountHeads = AccountHead::whereIn('name', ['Cash', 'bank', 'cash', 'Bank'])->get();

        $accountsList = [];
        $currentBalance = 0;
        $accountCode = $voucher->tel ?? '';

        $balanceService = app(\App\Services\BalanceService::class);

        if ($voucher->type === 'vendor') {
            $vendors = DB::table('vendors')->select('id', 'name', 'phone as mobile', 'address', 'opening_balance')->orderBy('name')->get();
            foreach ($vendors as $v) {
                $vBal = $balanceService->getVendorBalance($v->id);
                $lbl = $vBal >= 0 ? 'Cr' : 'Dr';
                $accountsList[] = (object) [
                    'id' => $v->id,
                    'title' => $v->name . " (Bal: " . number_format(abs($vBal), 0) . " {$lbl})",
                    'account_code' => $v->mobile ?? '',
                    'balance' => $vBal,
                ];
                if ((string)$v->id === (string)$voucher->party_id) {
                    $currentBalance = $vBal;
                    if (!$accountCode) $accountCode = $v->mobile ?? '';
                }
            }
        } elseif ($voucher->type === 'customer' || $voucher->type === 'walkin') {
            $custQuery = \App\Models\Customer::query();
            if ($voucher->type === 'walkin') {
                $custQuery->where('customer_type', 'Walking Customer');
            }
            $customers = $custQuery->orderBy('customer_name')->get();
            foreach ($customers as $c) {
                $cBal = $balanceService->getCustomerBalance($c->id);
                $lbl = $cBal >= 0 ? 'Dr' : 'Cr';
                $accountsList[] = (object) [
                    'id' => $c->id,
                    'title' => $c->customer_name . " (Bal: " . number_format(abs($cBal), 0) . " {$lbl})",
                    'account_code' => $c->mobile ?? '',
                    'balance' => $cBal,
                ];
                if ((string)$c->id === (string)$voucher->party_id) {
                    $currentBalance = $cBal;
                    if (!$accountCode) $accountCode = $c->mobile ?? '';
                }
            }
        } elseif (is_numeric($voucher->type)) {
            $accounts = Account::where('head_id', $voucher->type)->where('status', 1)->get();
            foreach ($accounts as $acc) {
                $bal = $acc->current_balance ?? $acc->opening_balance ?? 0;
                $accountsList[] = (object) [
                    'id' => $acc->id,
                    'title' => $acc->title,
                    'account_code' => $acc->account_code,
                    'balance' => $bal,
                ];
                if ((string)$acc->id === (string)$voucher->party_id) {
                    $currentBalance = $bal;
                    if (!$accountCode) $accountCode = $acc->account_code;
                }
            }
        }

        // Decode rows
        $narrIds = json_decode($voucher->narration_id, true);
        if (!is_array($narrIds)) $narrIds = $voucher->narration_id ? [$voucher->narration_id] : [];

        $accounts = json_decode($voucher->row_account_id, true);
        if (!is_array($accounts)) $accounts = $voucher->row_account_id ? [$voucher->row_account_id] : [];

        $amounts = json_decode($voucher->amount, true);
        if (!is_array($amounts)) $amounts = $voucher->amount ? [$voucher->amount] : [];

        $existingRows = [];
        $maxRows = max(count($narrIds), count($accounts), count($amounts), 1);
        for ($i = 0; $i < $maxRows; $i++) {
            $narrId = $narrIds[$i] ?? '';
            $narrText = '';
            if (!empty($narrId) && is_numeric($narrId)) {
                $narrText = DB::table('narrations')->where('id', $narrId)->value('narration') ?? '';
            } else {
                $narrText = (string) $narrId;
            }

            $existingRows[] = [
                'category_id' => $accounts[$i] ?? '',
                'narration_id' => $narrId,
                'narration_text' => $narrText,
                'amount' => isset($amounts[$i]) ? (float) $amounts[$i] : 0,
            ];
        }

        return view('admin_panel.vochers.expense_vochers.edit_expense_voucher', compact(
            'voucher',
            'narrations',
            'expenseCategories',
            'AccountHeads',
            'accountsList',
            'currentBalance',
            'accountCode',
            'existingRows'
        ));
    }

    public function update_expense_vochers(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $expense = ExpenseVoucher::findOrFail($id);

            $journalService = app(\App\Services\JournalEntryService::class);
            $balanceService = app(\App\Services\BalanceService::class);

            // 1. Revert previous accounting effects
            // A. Reverse all journal entries recorded for this voucher
            $journalService->reverseEntriesForSource($expense);

            // B. Reverse previous vendor / customer ledger if applicable
            $oldAmount = (float) $expense->total_amount;
            if ($expense->type === 'vendor' && $expense->party_id) {
                $oldLedger = VendorLedger::where('vendor_id', $expense->party_id)->latest()->first();
                if ($oldLedger) {
                    $oldLedger->closing_balance = $oldLedger->closing_balance + $oldAmount;
                    $oldLedger->save();
                }
            } elseif ($expense->type === 'customer' && $expense->party_id) {
                $oldLedger = CustomerLedger::where('customer_id', $expense->party_id)->latest()->first();
                if ($oldLedger) {
                    $oldLedger->closing_balance = $oldLedger->closing_balance + $oldAmount;
                    $oldLedger->save();
                }
            }

            // 2. Process narrations
            $narrationIds = [];
            if ($request->narration_id && is_array($request->narration_id)) {
                foreach ($request->narration_id as $index => $narrId) {
                    $manualText = $request->narration_text[$index] ?? null;

                    if (!empty($narrId) && is_numeric($narrId)) {
                        $existingNarr = \App\Models\Narration::find($narrId);
                        if ($existingNarr && !empty($manualText) && $existingNarr->narration !== $manualText) {
                            $existingNarr->update(['narration' => $manualText]);
                        }
                        $narrationIds[] = (string) $narrId;
                    } elseif (!empty($manualText)) {
                        $newNarr = \App\Models\Narration::create([
                            'expense_head' => 'Expense voucher',
                            'narration' => $manualText,
                        ]);
                        $narrationIds[] = (string) $newNarr->id;
                    } else {
                        $narrationIds[] = "";
                    }
                }
            }

            // 3. Update the expense voucher record
            $voucherData = [
                'entry_date' => $request->entry_date,
                'type' => $request->vendor_type,
                'party_id' => $request->vendor_id,
                'tel' => $request->tel,
                'remarks' => $request->remarks,
                'reference_no' => $request->ref_no_header,
                'narration_id' => json_encode($narrationIds),
                'row_account_head' => json_encode($request->row_account_head ?? array_fill(0, count($request->row_account_id ?? []), "0")),
                'row_account_id' => json_encode($request->row_account_id),
                'amount' => json_encode($request->amount),
                'total_amount' => $request->total_amount,
            ];

            $expense->update($voucherData);

            $amount = (float) $request->total_amount;
            $evid = $expense->evid;

            // 4. Record new journal entries
            // STEP A: Expense Accounts (Debit)
            $expenseHead = AccountHead::firstOrCreate(
                ['name' => 'Expense'],
                ['opening_balance' => 0]
            );

            $generalExpenseAccount = Account::firstOrCreate(
                ['account_code' => 'GEN-EXP'],
                [
                    'head_id' => $expenseHead->id,
                    'title' => 'General Expense',
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'type' => 'Debit',
                    'status' => 1
                ]
            );

            if ($request->row_account_id && $request->amount) {
                foreach ($request->row_account_id as $index => $accId) {
                    $rowAmount = isset($request->amount[$index]) ? (float) $request->amount[$index] : 0;
                    if ($rowAmount > 0) {
                        $categoryName = DB::table('expense_categories')->where('id', $accId)->value('name') ?? 'General Expense';
                        $journalService->recordEntry(
                            $expense,
                            $generalExpenseAccount->id,
                            $rowAmount, // Debit Expense
                            0, // Credit
                            "Expense Voucher #$evid ($categoryName)",
                            $request->entry_date ?? date('Y-m-d')
                        );
                    }
                }
            }

            // STEP B: Party side (Credit)
            if ($request->vendor_type === 'vendor') {
                $ledger = VendorLedger::where('vendor_id', $request->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance = $ledger->closing_balance - $amount;
                    $ledger->save();
                } else {
                    VendorLedger::create([
                        'vendor_id' => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'opening_balance' => 0,
                        'previous_balance' => 0,
                        'closing_balance' => -$amount,
                    ]);
                }

                $journalService->recordEntry(
                    $expense,
                    $balanceService->getAccountsPayableId(),
                    0,
                    $amount,
                    "Expense Voucher #$evid",
                    $request->entry_date ?? date('Y-m-d'),
                    \App\Models\Vendor::find($request->vendor_id)
                );
            } elseif ($request->vendor_type === 'customer') {
                $ledger = CustomerLedger::where('customer_id', $request->vendor_id)->latest()->first();
                if ($ledger) {
                    $ledger->previous_balance = $ledger->closing_balance;
                    $ledger->closing_balance = $ledger->closing_balance - $amount;
                    $ledger->save();
                } else {
                    CustomerLedger::create([
                        'customer_id' => $request->vendor_id,
                        'admin_or_user_id' => auth()->id(),
                        'previous_balance' => 0,
                        'opening_balance' => 0,
                        'closing_balance' => -$amount,
                    ]);
                }

                $journalService->recordEntry(
                    $expense,
                    $balanceService->getAccountsReceivableId(),
                    0,
                    $amount,
                    "Expense Voucher #$evid",
                    $request->entry_date ?? date('Y-m-d'),
                    \App\Models\Customer::find($request->vendor_id)
                );
            } else {
                $account = Account::find($request->vendor_id);
                if ($account) {
                    $journalService->recordEntry(
                        $expense,
                        $account->id,
                        0,
                        $amount,
                        "Expense Voucher #$evid",
                        $request->entry_date ?? date('Y-m-d')
                    );
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Expense Voucher updated successfully!',
                    'voucher_id' => $expense->id,
                    'print_url' => route('expenseprint', $expense->id),
                    'all_vouchers_url' => route('all_expense_vochers'),
                ]);
            }

            return redirect()->route('all_expense_vochers')->with('success', 'Expense Voucher updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Expense Voucher Update Error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function expenseprint($id)
    {
        $voucher = \App\Models\ExpenseVoucher::findOrFail($id);

        // Decode JSON arrays safely
        $narrations = json_decode($voucher->narration_id, true) ?? [];
        $references = json_decode($voucher->reference_no, true) ?? [];
        $accountHeads = json_decode($voucher->row_account_head, true) ?? [];
        $accounts = json_decode($voucher->row_account_id, true) ?? [];
        $amounts = json_decode($voucher->amount, true) ?? [];

        // 🧾 Prepare detailed rows
        $rows = [];
        foreach ($narrations as $index => $narrId) {
            $narration = DB::table('narrations')->where('id', $narrId)->value('narration');
            $ref = $references[$index] ?? null;

            // Check if it is a new custom category or a legacy account
            $accId = $accounts[$index] ?? null;
            $expenseCategory = DB::table('expense_categories')->where('id', $accId)->first();
            if ($expenseCategory) {
                $accountHead = 'Expense Category';
                $accountName = $expenseCategory->name;
                $accountCode = $expenseCategory->code;
            } else {
                $accountHead = DB::table('account_heads')->where('id', $accountHeads[$index] ?? null)->value('name');
                $account = DB::table('accounts')->where('id', $accId)->first();
                $accountName = $account->title ?? null;
                $accountCode = $account->account_code ?? null;
            }

            $amount = (float) ($amounts[$index] ?? 0);

            $rows[] = [
                'narration' => $narration,
                'reference' => $ref,
                'account_head' => $accountHead,
                'account_name' => $accountName,
                'account_code' => $accountCode,
                'amount' => $amount,
            ];
        }

        // 🧩 Party Setup Based on Type
        $party = null;
        $previousBalance = 0;

        if (is_numeric($voucher->type)) {
            // ✅ Account Head type (numeric)
            $accountHead = DB::table('account_heads')->where('id', $voucher->type)->first();
            $account = DB::table('accounts')->where('id', $voucher->party_id)->first();

            if ($account) {
                $party = (object) [
                    'name' => $account->title ?? '—',
                    'address' => '—',
                    'phone' => $account->account_code ?? '—',
                    'head_name' => $accountHead->name ?? '—',
                ];
            }

            $previousBalance = $account->opening_balance ?? 0;
        } elseif ($voucher->type === 'vendor') {
            // ✅ Vendor Type
            $party = DB::table('vendors')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('vendor_ledgers')
                ->where('vendor_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'customer') {
            // ✅ Customer Type
            $party = DB::table('customers')->where('id', $voucher->party_id)->first();
            $previousBalance = DB::table('customer_ledgers')
                ->where('customer_id', $voucher->party_id)
                ->orderByDesc('id')
                ->value('closing_balance') ?? 0;
        } elseif ($voucher->type === 'walkin') {
            // ✅ Walking Customer
            $party = DB::table('customers')
                ->where('id', $voucher->party_id)
                ->where('customer_type', 'Walking Customer')
                ->first();
        }

        return view('admin_panel.vochers.expense_vochers.print', compact('voucher', 'rows', 'party', 'previousBalance'));
    }

    public function fetchReceiptVouchers(Request $request)
    {

        // Fetch all accounts for the dropdown
        $accounts = \Illuminate\Support\Facades\DB::table('accounts')
            ->select('id', 'title', 'head_id')
            ->orderBy('title')
            ->get()
            ->map(function ($account) {
                // Get account head name
                $headName = \Illuminate\Support\Facades\DB::table('account_heads')
                    ->where('id', $account->head_id)
                    ->value('name');

                return [
                    'id' => $account->id,
                    'title' => $account->title,
                    'head_name' => $headName,
                    'display_name' => ($headName ? $headName.' - ' : '').$account->title,
                ];
            });

        return response()->json([
            'accounts' => $accounts,
        ]);
    }

    public function destroyReceiptVoucher($id)
    {
        DB::beginTransaction();
        try {
            // 1. Try finding V2 VoucherMaster
            $voucherMaster = \App\Models\VoucherMaster::where('id', $id)
                ->where('voucher_type', \App\Models\VoucherMaster::TYPE_RECEIPT)
                ->first();

            $legacyRec = null;
            if ($voucherMaster) {
                // Find matching legacy ReceiptsVoucher if exists
                $legacyRec = ReceiptsVoucher::where('rvid', $voucherMaster->voucher_no)
                    ->orWhere('remarks', 'like', "%{$voucherMaster->voucher_no}%")
                    ->first();
                if (!$legacyRec && preg_match('/Ref:\s*([^)]+)/', $voucherMaster->remarks ?? '', $m)) {
                    $legacyRec = ReceiptsVoucher::where('rvid', trim($m[1]))->first();
                }
            } else {
                // Check if $id is from legacy ReceiptsVoucher
                $legacyRec = ReceiptsVoucher::find($id);
                if ($legacyRec) {
                    $voucherMaster = \App\Models\VoucherMaster::where('voucher_type', \App\Models\VoucherMaster::TYPE_RECEIPT)
                        ->where(function($q) use ($legacyRec) {
                            $q->where('voucher_no', $legacyRec->rvid)
                              ->orWhere('remarks', 'like', "%{$legacyRec->rvid}%");
                        })
                        ->first();
                }
            }

            if (!$voucherMaster && !$legacyRec) {
                return back()->with('error', 'Receipt Voucher not found.');
            }

            $journalService = app(\App\Services\JournalEntryService::class);

            if ($voucherMaster) {
                // Reverse Journal Entries and update Account Balances
                $journalService->reverseEntriesForSource($voucherMaster);

                // Delete Details
                $voucherMaster->details()->delete();

                // Delete linked customer ledger entry if any
                $partyId = $voucherMaster->party_id;
                $vNo = $voucherMaster->voucher_no;
                if ($partyId && \Illuminate\Support\Facades\Schema::hasColumn('customer_ledgers', 'description')) {
                    CustomerLedger::where('customer_id', $partyId)
                        ->where(function($q) use ($vNo, $legacyRec) {
                            $q->where('description', 'like', "%{$vNo}%");
                            if ($legacyRec && $legacyRec->rvid) {
                                $q->orWhere('description', 'like', "%{$legacyRec->rvid}%");
                            }
                        })->delete();
                }

                $voucherMaster->delete();
            }

            if ($legacyRec) {
                $journalService->reverseEntriesForSource($legacyRec);
                $legacyRec->delete();
            }

            DB::commit();

            return back()->with('success', 'Receipt Voucher deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Receipt Voucher Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete Receipt Voucher: ' . $e->getMessage());
        }
    }

    public function destroyPaymentVoucher($id)
    {
        DB::beginTransaction();
        try {
            // 1. Try finding V2 VoucherMaster
            $voucherMaster = \App\Models\VoucherMaster::where('id', $id)
                ->where('voucher_type', \App\Models\VoucherMaster::TYPE_PAYMENT)
                ->first();

            $legacyPayment = null;
            if ($voucherMaster) {
                $legacyPayment = PaymentVoucher::where('pvid', $voucherMaster->voucher_no)
                    ->orWhere('remarks', 'like', "%{$voucherMaster->voucher_no}%")
                    ->first();
                if (!$legacyPayment && preg_match('/Ref:\s*([^)]+)/', $voucherMaster->remarks ?? '', $m)) {
                    $legacyPayment = PaymentVoucher::where('pvid', trim($m[1]))->first();
                }
            } else {
                $legacyPayment = PaymentVoucher::find($id);
                if ($legacyPayment) {
                    $voucherMaster = \App\Models\VoucherMaster::where('voucher_type', \App\Models\VoucherMaster::TYPE_PAYMENT)
                        ->where(function($q) use ($legacyPayment) {
                            $q->where('voucher_no', $legacyPayment->pvid)
                              ->orWhere('remarks', 'like', "%{$legacyPayment->pvid}%");
                        })
                        ->first();
                }
            }

            if (!$voucherMaster && !$legacyPayment) {
                return back()->with('error', 'Payment Voucher not found.');
            }

            $journalService = app(\App\Services\JournalEntryService::class);

            if ($voucherMaster) {
                // Reverse Journal Entries and update Account Balances
                $journalService->reverseEntriesForSource($voucherMaster);

                // Delete Details
                $voucherMaster->details()->delete();

                // Clean up customer ledger entries if applicable
                $partyId = $voucherMaster->party_id;
                $vNo = $voucherMaster->voucher_no;
                if ($partyId && \Illuminate\Support\Facades\Schema::hasColumn('customer_ledgers', 'description')) {
                    CustomerLedger::where('customer_id', $partyId)
                        ->where(function($q) use ($vNo, $legacyPayment) {
                            $q->where('description', 'like', "%{$vNo}%");
                            if ($legacyPayment && $legacyPayment->pvid) {
                                $q->orWhere('description', 'like', "%{$legacyPayment->pvid}%");
                            }
                        })->delete();
                }

                $voucherMaster->delete();
            }

            if ($legacyPayment) {
                $journalService->reverseEntriesForSource($legacyPayment);
                $legacyPayment->delete();
            }

            DB::commit();

            return back()->with('success', 'Payment Voucher deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment Voucher Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete Payment Voucher: ' . $e->getMessage());
        }
    }

    public function destroyExpenseVoucher($id)
    {
        DB::beginTransaction();
        try {
            $expense = ExpenseVoucher::find($id);

            if (!$expense) {
                return back()->with('error', 'Expense Voucher not found.');
            }

            $journalService = app(\App\Services\JournalEntryService::class);

            // 1. Reverse all journal entries (this restores cash/bank/general expense account balances)
            $journalService->reverseEntriesForSource($expense);

            // 2. Revert customer ledger if affected
            $evid = $expense->evid;
            if ($expense->type === 'customer' && $expense->party_id) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('customer_ledgers', 'description')) {
                    CustomerLedger::where('customer_id', $expense->party_id)
                        ->where('description', 'like', "%{$evid}%")
                        ->delete();
                }
            }

            // 3. Delete expense voucher
            $expense->delete();

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Expense Voucher deleted successfully.']);
            }
            return back()->with('success', 'Expense Voucher deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Expense Voucher Delete Error: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['message' => 'Failed to delete Expense Voucher: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to delete Expense Voucher: ' . $e->getMessage());
        }
    }

    /**
     * Unified All Vouchers Creation View
     */
    public function createUnified()
    {
        $cashBankHeadIds = DB::table('account_heads')
            ->where(function($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%cash%'])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%bank%']);
            })
            ->pluck('id');

        $accounts = DB::table('accounts')
            ->where('status', 1)
            ->where(function($q) use ($cashBankHeadIds) {
                $q->whereIn('head_id', $cashBankHeadIds)
                  ->orWhereRaw('LOWER(title) LIKE ?', ['%cash%'])
                  ->orWhereRaw('LOWER(title) LIKE ?', ['%bank%']);
            })
            ->whereNotIn('account_code', ['AR', 'AP', 'SALES', 'PURCHASE', 'GEN-EXP'])
            ->orderBy('title')
            ->get();

        $customers = DB::table('customers')
            ->select('id', 'customer_name', 'mobile', 'opening_balance')
            ->orderBy('customer_name')
            ->get();

        $vendors = DB::table('vendors')
            ->select('id', 'name', 'phone', 'opening_balance')
            ->orderBy('name')
            ->get();

        $expenseCategories = DB::table('expense_categories')
            ->orderBy('name')
            ->get();

        $lastExp = DB::table('expense_vouchers')->latest('id')->first();
        $nextEvid = 'EVID-' . str_pad($lastExp ? $lastExp->id + 1 : 1, 3, '0', STR_PAD_LEFT);

        return view('admin_panel.vochers.unified_create', compact(
            'accounts',
            'customers',
            'vendors',
            'expenseCategories',
            'nextEvid'
        ));
    }

    /**
     * Unified All Vouchers History View
     */
    public function voucherHistory()
    {
        $accounts = DB::table('accounts')
            ->where('status', 1)
            ->orderBy('title')
            ->get();

        return view('admin_panel.vochers.history', compact('accounts'));
    }

    /**
     * DataTables JSON for Unified All Vouchers History
     */
    public function voucherHistoryData(Request $request)
    {
        $type = $request->get('type', 'all');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $partyType = $request->get('party_type');
        $accountId = $request->get('account_id');
        $minAmount = $request->get('min_amount');
        $maxAmount = $request->get('max_amount');
        $searchValue = strtolower(trim($request->input('search.value') ?? ''));

        $user = auth()->user();
        $canDeleteExpense = $user ? ($user->can('expense.voucher.delete') || $user->can('all.vouchers.delete')) : false;
        $canDeleteReceipt = $user ? ($user->can('receipts.voucher.delete') || $user->can('all.vouchers.delete')) : false;
        $canDeletePayment = $user ? ($user->can('payment.voucher.delete') || $user->can('all.vouchers.delete')) : false;

        $records = collect();

        // 1. Expense Vouchers
        if ($type === 'all' || $type === 'expense') {
            $evQuery = DB::table('expense_vouchers');

            if ($fromDate) {
                $evQuery->whereDate('entry_date', '>=', $fromDate);
            }
            if ($toDate) {
                $evQuery->whereDate('entry_date', '<=', $toDate);
            }
            if ($partyType) {
                if ($partyType === 'customer') {
                    $evQuery->whereIn('type', ['customer', 'walkin']);
                } elseif ($partyType === 'vendor') {
                    $evQuery->where('type', 'vendor');
                }
            }
            if ($accountId) {
                $evQuery->where(function($q) use ($accountId) {
                    $q->where('row_account_id', 'like', '%"' . $accountId . '"%')
                      ->orWhere('row_account_id', 'like', '%[' . $accountId . ']%')
                      ->orWhere('row_account_id', 'like', '%,' . $accountId . ',%');
                });
            }
            if ($minAmount !== null && $minAmount !== '') {
                $evQuery->where('total_amount', '>=', (float)$minAmount);
            }
            if ($maxAmount !== null && $maxAmount !== '') {
                $evQuery->where('total_amount', '<=', (float)$maxAmount);
            }

            $expenseList = $evQuery->orderBy('id', 'desc')->get();

            $customerIds = $expenseList->whereIn('type', ['customer', 'walkin'])->pluck('party_id')->unique()->filter();
            $vendorIds = $expenseList->where('type', 'vendor')->pluck('party_id')->unique()->filter();
            $accountIds = $expenseList->whereNotIn('type', ['customer', 'walkin', 'vendor'])->pluck('party_id')->unique()->filter();

            $customers = DB::table('customers')->whereIn('id', $customerIds)->pluck('customer_name', 'id');
            $vendors = DB::table('vendors')->whereIn('id', $vendorIds)->pluck('name', 'id');
            $accountsMap = DB::table('accounts')->whereIn('id', $accountIds)->pluck('title', 'id');

            foreach ($expenseList as $ev) {
                $pName = '-';
                $pType = '-';
                if ($ev->type === 'vendor') {
                    $pName = $vendors[$ev->party_id] ?? 'Vendor #' . $ev->party_id;
                    $pType = 'Vendor';
                } elseif ($ev->type === 'customer' || $ev->type === 'walkin') {
                    $pName = $customers[$ev->party_id] ?? 'Customer #' . $ev->party_id;
                    $pType = 'Customer';
                } elseif (is_numeric($ev->type)) {
                    $pName = $accountsMap[$ev->party_id] ?? 'Account #' . $ev->party_id;
                    $pType = 'Account';
                }

                // Determine expense category or account name
                $accIds = json_decode($ev->row_account_id, true);
                $detailText = 'Expense Account';
                if (is_array($accIds) && !empty($accIds)) {
                    $firstId = $accIds[0];
                    $catName = DB::table('expense_categories')->where('id', $firstId)->value('name');
                    if (!$catName) {
                        $catName = DB::table('accounts')->where('id', $firstId)->value('title');
                    }
                    if ($catName) $detailText = $catName;
                }
                if ($detailText === 'Expense Account' && $ev->reference_no) {
                    $detailText = 'Ref: ' . $ev->reference_no;
                }

                $canDelete = $canDeleteExpense;

                $records->push([
                    'id' => $ev->id,
                    'voucher_no' => $ev->evid ?: 'EV-' . $ev->id,
                    'type_label' => 'Expense',
                    'source' => 'expense',
                    'date' => $ev->entry_date ? date('Y-m-d', strtotime($ev->entry_date)) : '-',
                    'party_name' => $pName,
                    'party_type_label' => $pType,
                    'detail' => $detailText,
                    'amount' => (float)$ev->total_amount,
                    'remarks' => $ev->remarks ?: '-',
                    'print_url' => route('expenseprint', $ev->id),
                    'delete_url' => $canDelete ? route('expense_vouchers.destroy', $ev->id) : null,
                    'delete_method' => 'DELETE',
                    'created_at' => $ev->created_at ?? $ev->entry_date,
                ]);
            }
        }

        // 2. Receipt Vouchers (Payment In)
        if ($type === 'all' || $type === 'payment_in') {
            $rvQuery = DB::table('voucher_masters')->where('voucher_type', 'receipt');

            if ($fromDate) {
                $rvQuery->whereDate('date', '>=', $fromDate);
            }
            if ($toDate) {
                $rvQuery->whereDate('date', '<=', $toDate);
            }
            if ($partyType) {
                if ($partyType === 'customer') {
                    $rvQuery->where('party_type', 'like', '%Customer%');
                } elseif ($partyType === 'vendor') {
                    $rvQuery->where('party_type', 'like', '%Vendor%');
                }
            }
            if ($accountId) {
                $matchingMasterIds = DB::table('voucher_details')->where('account_id', $accountId)->pluck('voucher_master_id');
                $rvQuery->whereIn('id', $matchingMasterIds);
            }
            if ($minAmount !== null && $minAmount !== '') {
                $rvQuery->where('total_amount', '>=', (float)$minAmount);
            }
            if ($maxAmount !== null && $maxAmount !== '') {
                $rvQuery->where('total_amount', '<=', (float)$maxAmount);
            }

            $receiptList = $rvQuery->orderBy('id', 'desc')->get();

            $custIds = $receiptList->filter(fn($v) => str_contains($v->party_type ?? '', 'Customer'))->pluck('party_id')->unique();
            $vendIds = $receiptList->filter(fn($v) => str_contains($v->party_type ?? '', 'Vendor'))->pluck('party_id')->unique();
            $accIds = $receiptList->filter(fn($v) => str_contains($v->party_type ?? '', 'Account'))->pluck('party_id')->unique();

            $customers = DB::table('customers')->whereIn('id', $custIds)->pluck('customer_name', 'id');
            $vendors = DB::table('vendors')->whereIn('id', $vendIds)->pluck('name', 'id');
            $accountsMap = DB::table('accounts')->whereIn('id', $accIds)->pluck('title', 'id');

            // Find Cash/Bank Accounts used in Receipt Voucher details (where debit > 0)
            $receiptAccounts = DB::table('voucher_details')
                ->join('accounts', 'accounts.id', '=', 'voucher_details.account_id')
                ->whereIn('voucher_details.voucher_master_id', $receiptList->pluck('id'))
                ->where('voucher_details.debit', '>', 0)
                ->select('voucher_details.voucher_master_id', 'accounts.title')
                ->get()
                ->keyBy('voucher_master_id');

            foreach ($receiptList as $rv) {
                $pName = '-';
                $pType = '-';
                if (str_contains($rv->party_type ?? '', 'Customer')) {
                    $pName = $customers[$rv->party_id] ?? 'Customer #' . $rv->party_id;
                    $pType = 'Customer';
                } elseif (str_contains($rv->party_type ?? '', 'Vendor')) {
                    $pName = $vendors[$rv->party_id] ?? 'Vendor #' . $rv->party_id;
                    $pType = 'Vendor';
                } elseif (str_contains($rv->party_type ?? '', 'Account')) {
                    $pName = $accountsMap[$rv->party_id] ?? 'Account #' . $rv->party_id;
                    $pType = 'Account';
                }

                $canDelete = $canDeleteReceipt;
                $detailName = isset($receiptAccounts[$rv->id]) ? $receiptAccounts[$rv->id]->title : 'Receipt';

                $records->push([
                    'id' => $rv->id,
                    'voucher_no' => $rv->voucher_no ?: 'RV-' . $rv->id,
                    'type_label' => 'Payment In',
                    'source' => 'payment_in',
                    'date' => $rv->date ? date('Y-m-d', strtotime($rv->date)) : '-',
                    'party_name' => $pName,
                    'party_type_label' => $pType,
                    'detail' => $detailName,
                    'amount' => (float)$rv->total_amount,
                    'remarks' => $rv->remarks ?: '-',
                    'print_url' => route('print', $rv->id),
                    'delete_url' => $canDelete ? route('receipt_vouchers.destroy', $rv->id) : null,
                    'delete_method' => 'DELETE',
                    'created_at' => $rv->created_at ?? $rv->date,
                ]);
            }
        }

        // 3. Payment Vouchers (Payment Out)
        if ($type === 'all' || $type === 'payment_out') {
            $pvQuery = DB::table('voucher_masters')->where('voucher_type', 'payment');

            if ($fromDate) {
                $pvQuery->whereDate('date', '>=', $fromDate);
            }
            if ($toDate) {
                $pvQuery->whereDate('date', '<=', $toDate);
            }
            if ($partyType) {
                if ($partyType === 'customer') {
                    $pvQuery->where('party_type', 'like', '%Customer%');
                } elseif ($partyType === 'vendor') {
                    $pvQuery->where('party_type', 'like', '%Vendor%');
                }
            }
            if ($accountId) {
                $matchingMasterIds = DB::table('voucher_details')->where('account_id', $accountId)->pluck('voucher_master_id');
                $pvQuery->whereIn('id', $matchingMasterIds);
            }
            if ($minAmount !== null && $minAmount !== '') {
                $pvQuery->where('total_amount', '>=', (float)$minAmount);
            }
            if ($maxAmount !== null && $maxAmount !== '') {
                $pvQuery->where('total_amount', '<=', (float)$maxAmount);
            }

            $paymentList = $pvQuery->orderBy('id', 'desc')->get();

            $custIds = $paymentList->filter(fn($v) => str_contains($v->party_type ?? '', 'Customer'))->pluck('party_id')->unique();
            $vendIds = $paymentList->filter(fn($v) => str_contains($v->party_type ?? '', 'Vendor'))->pluck('party_id')->unique();
            $accIds = $paymentList->filter(fn($v) => str_contains($v->party_type ?? '', 'Account'))->pluck('party_id')->unique();

            $customers = DB::table('customers')->whereIn('id', $custIds)->pluck('customer_name', 'id');
            $vendors = DB::table('vendors')->whereIn('id', $vendIds)->pluck('name', 'id');
            $accountsMap = DB::table('accounts')->whereIn('id', $accIds)->pluck('title', 'id');

            // Find Cash/Bank Accounts used in Payment Voucher details (where credit > 0)
            $paymentAccounts = DB::table('voucher_details')
                ->join('accounts', 'accounts.id', '=', 'voucher_details.account_id')
                ->whereIn('voucher_details.voucher_master_id', $paymentList->pluck('id'))
                ->where('voucher_details.credit', '>', 0)
                ->select('voucher_details.voucher_master_id', 'accounts.title')
                ->get()
                ->keyBy('voucher_master_id');

            foreach ($paymentList as $pv) {
                $pName = '-';
                $pType = '-';
                if (str_contains($pv->party_type ?? '', 'Vendor')) {
                    $pName = $vendors[$pv->party_id] ?? 'Vendor #' . $pv->party_id;
                    $pType = 'Vendor';
                } elseif (str_contains($pv->party_type ?? '', 'Customer')) {
                    $pName = $customers[$pv->party_id] ?? 'Customer #' . $pv->party_id;
                    $pType = 'Customer';
                } elseif (str_contains($pv->party_type ?? '', 'Account')) {
                    $pName = $accountsMap[$pv->party_id] ?? 'Account #' . $pv->party_id;
                    $pType = 'Account';
                }

                $canDelete = $canDeletePayment;
                $detailName = isset($paymentAccounts[$pv->id]) ? $paymentAccounts[$pv->id]->title : 'Payment';

                $records->push([
                    'id' => $pv->id,
                    'voucher_no' => $pv->voucher_no ?: 'PV-' . $pv->id,
                    'type_label' => 'Payment Out',
                    'source' => 'payment_out',
                    'date' => $pv->date ? date('Y-m-d', strtotime($pv->date)) : '-',
                    'party_name' => $pName,
                    'party_type_label' => $pType,
                    'detail' => $detailName,
                    'amount' => (float)$pv->total_amount,
                    'remarks' => $pv->remarks ?: '-',
                    'print_url' => route('Paymentprint', $pv->id),
                    'delete_url' => $canDelete ? route('payment_vouchers.destroy', $pv->id) : null,
                    'delete_method' => 'DELETE',
                    'created_at' => $pv->created_at ?? $pv->date,
                ]);
            }
        }

        // Global search filtering
        if (!empty($searchValue)) {
            $records = $records->filter(function($item) use ($searchValue) {
                return str_contains(strtolower($item['voucher_no']), $searchValue)
                    || str_contains(strtolower($item['party_name']), $searchValue)
                    || str_contains(strtolower($item['party_type_label']), $searchValue)
                    || str_contains(strtolower($item['remarks']), $searchValue)
                    || str_contains(strtolower($item['detail']), $searchValue)
                    || str_contains((string)$item['amount'], $searchValue)
                    || str_contains(strtolower($item['date']), $searchValue);
            });
        }

        // Calculate summaries
        $totalAmount = $records->sum('amount');
        $totalExpense = $records->where('source', 'expense')->sum('amount');
        $totalPaymentIn = $records->where('source', 'payment_in')->sum('amount');
        $totalPaymentOut = $records->where('source', 'payment_out')->sum('amount');

        // Sort records by date descending
        $sorted = $records->sortByDesc(function($item) {
            return $item['date'] . ' ' . ($item['created_at'] ?? '');
        })->values();

        $totalCount = $sorted->count();
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 25);
        if ($length > 0) {
            $pagedData = $sorted->slice($start, $length)->values();
        } else {
            $pagedData = $sorted;
        }

        return response()->json([
            'draw' => (int)$request->input('draw', 1),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
            'data' => $pagedData,
            'summary' => [
                'total_amount' => $totalAmount,
                'total_expense' => $totalExpense,
                'total_income' => 0,
                'total_payment_in' => $totalPaymentIn,
                'total_payment_out' => $totalPaymentOut,
            ]
        ]);
    }
}

