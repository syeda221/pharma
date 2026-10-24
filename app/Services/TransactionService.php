<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\VoucherMaster;

class TransactionService
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * Create a Receipt Voucher from a Posted Sale using V2 standard.
     */
    /**
     * Create a Receipt Voucher from a Posted Sale using V2 standard.
     * Supports split payments (multiple accounts).
     */
    public function createReceiptFromSale(Sale $sale, array $accountIds = [], array $amounts = [], $changeAccountId = null)
    {
        \Log::info("TransactionService V2: Called for Sale ID {$sale->id}");

        // 1. Validation
        if ($sale->sale_status !== 'posted') {
            \Log::warning('TransactionService: Not posted, aborting.');

            return;
        }

        // Filter out empty or invalid entries
        $accountIds = array_filter($accountIds, function ($value) {
            return ! empty($value);
        });

        // Determine if we have valid input arrays, otherwise fallback to Sale Cash
        if (empty($accountIds)) {
            // Fallback: Check if Sale has a cash value (Legacy support or Hidden Input)
            $cash = $sale->cash ?? 0;
            if ($cash > 0) {
                \Log::info("TransactionService: Using fallback Cash amount: $cash");
                $balanceService = app(\App\Services\BalanceService::class);
                $accountIds = [$balanceService->getCashAccountId()];
                $amounts = [$cash];
            } else {
                \Log::info('TransactionService: No payment info provided (Credit Sale), aborting receipt creation.');

                return;
            }
        }

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            $customerControlAccountId = $balanceService->getAccountsReceivableId();
            $totalReceived = 0;
            $lines = [];

            // 2. Prepare Debit Lines (Money In - accounts customer paid into)
            foreach ($accountIds as $index => $accId) {
                $amount = (float) ($amounts[$index] ?? 0);
                if ($amount > 0) {
                    $totalReceived += $amount;

                    $lines[] = [
                        'account_id' => $accId,
                        'debit' => $amount,
                        'credit' => 0,
                        'narration' => "Payment received from Invoice #{$sale->invoice_no}",
                    ];
                }
            }

            if ($totalReceived <= 0) {
                \Log::info('TransactionService: Total received amount is 0, skipping receipt voucher.');

                return;
            }

            // 3. Handle Change Given (Money Out from chosen change account)
            $changeAmount = 0;
            if ($sale->change > 0) {
                $changeAmount = (float) $sale->change;
            }

            if ($changeAmount > 0) {
                $actualChangeAccId = $changeAccountId ?: ($sale->change_account_id ?: $balanceService->getCashAccountId());
                
                $lines[] = [
                    'account_id' => $actualChangeAccId,
                    'debit' => 0,
                    'credit' => $changeAmount,
                    'narration' => "Change returned to customer for Invoice #{$sale->invoice_no}",
                ];
            }

            // 4. Net settlement applied to invoice / customer receivable
            $netPaid = $totalReceived - $changeAmount;
            if ($netPaid > 0) {
                $creditAccountId = $sale->customer_id ? $customerControlAccountId : $balanceService->getSalesRevenueId();
                $lines[] = [
                    'account_id' => $creditAccountId,
                    'debit' => 0,
                    'credit' => $netPaid,
                    'narration' => "Payment for Invoice #{$sale->invoice_no}",
                ];
            }

            // 5. Voucher Header
            $voucherData = [
                'voucher_type' => VoucherMaster::TYPE_RECEIPT,
                'date' => now()->format('Y-m-d'),
                'status' => VoucherMaster::STATUS_POSTED, // Auto-post
                'payment_from' => $sale->customer_id ? 'Customer' : 'Walk-in',
                'party_type' => $sale->customer_id ? Customer::class : null,
                'party_id' => $sale->customer_id,
                'remarks' => "Auto-Receipt for Sale Invoice #{$sale->invoice_no}. Received: $totalReceived" . ($changeAmount > 0 ? " (Change: $changeAmount)" : ""),
            ];

            // 6. Create via VoucherService
            $voucher = $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            // 7. SYNC TO LEGACY CUSTOMER LEDGER (Critical for "Customer Balance" view)
            if ($sale->customer_id) {
                $lastEntry = \App\Models\CustomerLedger::where('customer_id', $sale->customer_id)
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();

                $prevBal = $lastEntry ? $lastEntry->closing_balance : 0;
                $newBal = $prevBal - $netPaid;

                \Log::info("Legacy Ledger (Receipt): Customer #{$sale->customer_id}. Prev: {$prevBal} - Paid: {$netPaid} = New: {$newBal}");

                \App\Models\CustomerLedger::create([
                    'customer_id' => $sale->customer_id,
                    'admin_or_user_id' => auth()->id() ?? 1,
                    'description' => "Receipt #{$voucher->voucher_no} for Invoice #{$sale->invoice_no}",
                    'previous_balance' => $prevBal,
                    'closing_balance' => $newBal,
                    'opening_balance' => 0,
                ]);

                $cust = \App\Models\Customer::find($sale->customer_id);
                if ($cust) {
                    $cust->previous_balance = $newBal;
                    $cust->save();
                }
            }

            \Log::info("TransactionService: V2 Receipt Created: {$voucher->voucher_no} for net amount $netPaid (Received: $totalReceived, Change: $changeAmount)");

            return $voucher->voucher_no;

        } catch (\Exception $e) {
            \Log::error('TransactionService V2 Error: '.$e->getMessage());
        }
    }

    /**
     * Create a Payment Voucher for a Purchase.
     * Debit: Accounts Payable (Vendor) | Credit: Cash/Bank
     */
    public function createPaymentForPurchase(\App\Models\Purchase $purchase, array $accountIds = [], array $amounts = [])
    {
        \Log::info("TransactionService: Create Payment for Purchase #{$purchase->invoice_no}");

        // Filter valid inputs
        $accountIds = array_filter($accountIds, fn ($val) => ! empty($val));

        if (empty($accountIds)) {
            \Log::info('TransactionService: No payment accounts provided, skipping payment.');

            return;
        }

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            $apAccountId = $balanceService->getAccountsPayableId(); // We need to ensure this method exists

            $totalPaid = 0;
            $lines = [];

            // 1. Prepare Credit Lines (Money Out - Cash/Bank)
            foreach ($accountIds as $index => $accId) {
                $amount = (float) ($amounts[$index] ?? 0);
                if ($amount > 0) {
                    $totalPaid += $amount;
                    $lines[] = [
                        'account_id' => $accId,
                        'debit' => 0,
                        'credit' => $amount, // Money leaving asset
                        'narration' => "Payment for Purchase #{$purchase->invoice_no}",
                    ];
                }
            }

            if ($totalPaid <= 0) {
                return;
            }

            // 2. Prepare Debit Line (Accounts Payable - Liability Decreases)
            $vendorName = '';
            if ($purchase->vendor) {
                $vendorName = $purchase->vendor->name;
            }

            $lines[] = [
                'account_id' => $apAccountId,
                'debit' => $totalPaid,
                'credit' => 0,
                'narration' => "Payment to Vendor {$vendorName}",
            ];

            // 3. Voucher Header
            $voucherData = [
                'voucher_type' => VoucherMaster::TYPE_PAYMENT,
                'date' => now()->format('Y-m-d'),
                'status' => VoucherMaster::STATUS_POSTED,
                'payment_from' => 'Vendor', // Or 'System'
                'party_type' => \App\Models\Vendor::class,
                'party_id' => $purchase->vendor_id,
                'remarks' => "Auto-Payment for Purchase #{$purchase->invoice_no}",
            ];

            // 4. Create Voucher
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            // 5. Update Legacy Vendor Ledger (Critical for consistency)
            if ($purchase->vendor_id) {
                // Fetch latest ledger to get current balance
                $lastEntry = \App\Models\VendorLedger::where('vendor_id', $purchase->vendor_id)
                    ->lockForUpdate() 
                    ->orderBy('id', 'desc')
                    ->first();

                $prevBal = $lastEntry ? $lastEntry->closing_balance : 0;
                // Payment reduces payable balance (Debit Vendor)
                // Note: Vendor balance in this system seems to be Credit-based (Payable = Positive)
                $newBal = $prevBal - $totalPaid;

                \Log::info("Legacy Ledger (Payment): Vendor #{$purchase->vendor_id}. Prev: {$prevBal} - Paid: {$totalPaid} = New: {$newBal}");

                \App\Models\VendorLedger::updateOrCreate(
                    ['vendor_id' => $purchase->vendor_id],
                    [
                        'admin_or_user_id' => auth()->id() ?? 1,
                        'previous_balance' => $prevBal,
                        'closing_balance' => $newBal,
                        'opening_balance' => $lastEntry ? $lastEntry->opening_balance : 0,
                    ]
                );
            }

            // Update Paid Amount in Purchase
            $purchase->paid_amount = $totalPaid;
            $purchase->due_amount = max(0, $purchase->net_amount - $purchase->paid_amount);
            $purchase->save();

            \Log::info("Payment Voucher Created for Purchase #{$purchase->invoice_no}");

        } catch (\Exception $e) {
            \Log::error('TransactionService Payment Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function createPurchaseVoucher(\App\Models\Purchase $purchase)
    {
        \Log::info("TransactionService: Create Voucher for Purchase #{$purchase->invoice_no}");

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            $expenseAccountId = $balanceService->getPurchaseExpenseId();
            $apAccountId = $balanceService->getAccountsPayableId();

            $lines = [];

            // 1. Debit Purchase Expense
            $lines[] = [
                'account_id' => $expenseAccountId,
                'debit' => $purchase->net_amount,
                'credit' => 0,
                'narration' => "Purchase Invoice #{$purchase->invoice_no}",
            ];

            // 2. Credit Accounts Payable (Vendor Liability)
            $lines[] = [
                'account_id' => $apAccountId,
                'debit' => 0,
                'credit' => $purchase->net_amount,
                'narration' => "Payable to Vendor " . ($purchase->vendor->name ?? ''),
            ];

            // 3. Voucher Header
            // Using TYPE_PAYMENT so it appears in 'all_Payment_vochers' listing as requested
            $voucherData = [
                'voucher_type' => \App\Models\VoucherMaster::TYPE_JOURNAL, 
                'date' => $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'status' => \App\Models\VoucherMaster::STATUS_POSTED,
                'party_type' => \App\Models\Vendor::class,
                'party_id' => $purchase->vendor_id,
                'remarks' => "Purchase Voucher #{$purchase->invoice_no}",
            ];

            // 4. Create Voucher
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            \Log::info("Purchase Voucher Created for Invoice #{$purchase->invoice_no}");

        } catch (\Exception $e) {
            \Log::error('TransactionService Purchase Voucher Error: ' . $e->getMessage());
            // We log but don't rethrow to avoid blocking main flow if config is missing
        }
    }
    
    /**
     * Create a Purchase Return Voucher (Debit Note).
     * Debit: Accounts Payable (Vendor) | Credit: Purchase Return / Inventory
     */
    public function createPurchaseReturnVoucher(\App\Models\PurchaseReturn $return)
    {
        \Log::info("TransactionService: Create Voucher for Purchase Return #{$return->return_invoice}");

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            // Use Purchase Expense Account (Contra) or a specific Return Account
            $expenseAccountId = $balanceService->getPurchaseExpenseId(); 
            $apAccountId = $balanceService->getAccountsPayableId();

            $lines = [];

            // 1. Debit Accounts Payable (Vendor Liability Reduces)
            $lines[] = [
                'account_id' => $apAccountId,
                'debit' => $return->net_amount,
                'credit' => 0,
                'narration' => "Debit Note for Return #{$return->return_invoice}",
            ];

            // 2. Credit Purchase Expense (Inventory Value Reduces)
            $lines[] = [
                'account_id' => $expenseAccountId,
                'debit' => 0,
                'credit' => $return->net_amount,
                'narration' => "Purchase Return #{$return->return_invoice}",
            ];

            // 3. Voucher Header
            // Use Journal Type or a specific 'Debit Note' type if available. 
            // Using TYPE_JOURNAL for general ledger adjustment.
            $voucherData = [
                'voucher_type' => \App\Models\VoucherMaster::TYPE_JOURNAL, 
                'date' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'status' => \App\Models\VoucherMaster::STATUS_POSTED,
                'party_type' => \App\Models\Vendor::class,
                'party_id' => $return->vendor_id,
                'remarks' => $return->remarks ?? "Purchase Return #{$return->return_invoice}",
            ];

            // 4. Create Voucher
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            \Log::info("Purchase Return Voucher Created for Invoice #{$return->return_invoice}");

        } catch (\Exception $e) {
            \Log::error('TransactionService Purchase Return Voucher Error: ' . $e->getMessage());
        }
    }

    /**
     * Create Journal Voucher for Sale Return (Credit Note)
     * Dr. Sales Revenue (Reduces Income)
     * Cr. Accounts Receivable (Reduces Customer Debt)
     */
    public function createSaleReturnVoucher(\App\Models\SaleReturn $return)
    {
        \Log::info("TransactionService: Create Voucher for Sale Return #{$return->return_invoice}");

        try {
            $balanceService = app(\App\Services\BalanceService::class);
            // Sales Revenue Account
            $salesRevenueId = $balanceService->getSalesRevenueId(); 
            $arAccountId = $balanceService->getAccountsReceivableId();

            $lines = [];

            // 1. Debit Sales Revenue (Income Reduces)
            $lines[] = [
                'account_id' => $salesRevenueId,
                'debit' => $return->net_amount,
                'credit' => 0,
                'narration' => "Credit Note for Return #{$return->return_invoice}",
            ];

            // 2. Credit Accounts Receivable (Customer Debt Reduces)
            $lines[] = [
                'account_id' => $arAccountId,
                'debit' => 0,
                'credit' => $return->net_amount,
                'narration' => "Sale Return #{$return->return_invoice}",
            ];

            // 3. Voucher Header
            $voucherData = [
                'voucher_type' => \App\Models\VoucherMaster::TYPE_JOURNAL, 
                'date' => $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'status' => \App\Models\VoucherMaster::STATUS_POSTED,
                'party_type' => \App\Models\Customer::class,
                'party_id' => $return->customer_id,
                'remarks' => "Sale Return #{$return->return_invoice}" . (!empty($return->remarks) ? " - {$return->remarks}" : ""),
            ];

            // 4. Create Voucher
            $this->voucherService->createVoucher($voucherData, $lines, auth()->id());

            \Log::info("Sale Return Voucher Created for Invoice #{$return->return_invoice}");

        } catch (\Exception $e) {
            \Log::error('TransactionService Sale Return Voucher Error: ' . $e->getMessage());
        }
    }
}
