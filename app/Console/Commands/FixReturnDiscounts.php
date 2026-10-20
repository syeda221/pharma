<?php

namespace App\Console\Commands;

use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\VoucherMaster;
use App\Services\BalanceService;
use App\Services\JournalEntryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixReturnDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'returns:fix-discount 
                            {--dry-run : Preview changes without modifying the database} 
                            {--execute : Apply updates to sale returns, items, and accounting vouchers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and fix past sale returns where item discount was omitted';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isExecute = $this->option('execute');

        // Default to dry-run if neither is explicitly passed
        if (!$isDryRun && !$isExecute) {
            $isDryRun = true;
            $this->info("No flag provided. Running in [DRY-RUN] preview mode by default.");
            $this->info("Use --execute to actually apply changes.\n");
        }

        $this->info("Scanning all Sale Returns linked to Sales...");

        $returns = SaleReturn::with(['items.product', 'sale.items', 'customer'])
            ->whereNotNull('sale_id')
            ->orderBy('id', 'asc')
            ->get();

        if ($returns->isEmpty()) {
            $this->info("No sale returns found linked to a sale invoice.");
            return 0;
        }

        $tableData = [];
        $totalOverstated = 0;
        $affectedCount = 0;
        $updatesToProcess = [];

        foreach ($returns as $return) {
            $sale = $return->sale;
            if (!$sale) continue;

            $hasDiscrepancy = false;
            $newReturnItemsData = [];
            $newSubtotal = 0;
            $newTotalItemDiscount = 0;

            foreach ($return->items as $returnItem) {
                // Find matching sale item
                $saleItem = $sale->items->where('product_id', $returnItem->product_id)->first();
                
                // Existing gross is line_total + current item_discount
                $lineGross = round((float)$returnItem->line_total + (float)$returnItem->item_discount, 2);

                if (!$saleItem) {
                    $newSubtotal += $lineGross;
                    $newTotalItemDiscount += (float)$returnItem->item_discount;
                    $newReturnItemsData[] = [
                        'item' => $returnItem,
                        'disc' => (float)$returnItem->item_discount,
                        'gross' => $lineGross,
                        'line_total' => (float)$returnItem->line_total,
                    ];
                    continue;
                }

                // Compute discount rate from original sale item
                $discPercent = (float)($saleItem->discount_percent ?? 0);
                $discAmount = (float)($saleItem->discount_amount ?? 0);
                $salePurchasedQty = (float)($saleItem->qty > 0 ? $saleItem->qty : 1);
                $unitPrice = (float)($returnItem->price > 0 ? $returnItem->price : ($saleItem->price ?? $saleItem->per_price ?? 0));

                $expectedItemDisc = 0;
                if ($discPercent > 0) {
                    $expectedItemDisc = round(($lineGross * $discPercent) / 100, 2);
                } elseif ($discAmount > 0) {
                    $billableQty = ($unitPrice > 0) ? round($lineGross / $unitPrice, 4) : (float)$returnItem->qty;
                    $discPerPiece = round($discAmount / $salePurchasedQty, 4);
                    $expectedItemDisc = round($discPerPiece * $billableQty, 2);
                }

                if ($expectedItemDisc > $lineGross) {
                    $expectedItemDisc = $lineGross;
                }
                $expectedLineTotal = round(max(0, $lineGross - $expectedItemDisc), 2);

                $currentItemDisc = (float)$returnItem->item_discount;
                if (abs($expectedItemDisc - $currentItemDisc) >= 0.01) {
                    $hasDiscrepancy = true;
                }

                $newSubtotal += $lineGross;
                $newTotalItemDiscount += $expectedItemDisc;

                $newReturnItemsData[] = [
                    'item' => $returnItem,
                    'disc' => $expectedItemDisc,
                    'gross' => $lineGross,
                    'line_total' => $expectedLineTotal,
                ];
            }

            $extraDisc = (float)($return->extra_discount ?? 0);
            $newNetAmount = round(max(0, ($newSubtotal - $newTotalItemDiscount) - $extraDisc), 2);
            $oldNetAmount = (float)$return->net_amount;
            $diff = round($oldNetAmount - $newNetAmount, 2);

            if ($hasDiscrepancy || abs($diff) >= 0.01) {
                $affectedCount++;
                $totalOverstated += $diff;

                $customerName = $return->customer->customer_name ?? 'Walking / Unknown';

                $tableData[] = [
                    'invoice' => $return->return_invoice,
                    'sale_invoice' => $sale->sale_invoice ?? "#{$sale->id}",
                    'customer' => mb_strimwidth($customerName, 0, 18, '...'),
                    'old_net' => number_format($oldNetAmount, 2),
                    'expected_disc' => number_format($newTotalItemDiscount, 2),
                    'new_net' => number_format($newNetAmount, 2),
                    'diff' => number_format($diff, 2),
                ];

                $updatesToProcess[] = [
                    'return' => $return,
                    'new_subtotal' => $newSubtotal,
                    'new_total_item_disc' => $newTotalItemDiscount,
                    'new_net_amount' => $newNetAmount,
                    'diff' => $diff,
                    'items_data' => $newReturnItemsData,
                ];
            }
        }

        if ($affectedCount === 0) {
            $this->info("All past sale returns are already correctly discounted. No discrepancies found.");
            return 0;
        }

        // Display Summary Table
        $this->table(
            ['Return #', 'Sale #', 'Customer', 'Old Net (Refunded)', 'New Discount', 'Correct Net', 'Difference (Excess Credit)'],
            $tableData
        );

        $this->line("");
        $this->warn("Summary of Discrepancies:");
        $this->line("Total Returns Affected : <fg=yellow>{$affectedCount}</>");
        $this->line("Total Excess Refunded   : <fg=red>" . number_format($totalOverstated, 2) . "</>");
        $this->line("");

        if ($isDryRun && !$isExecute) {
            $this->info("------------------------------------------------------------------------");
            $this->info("[DRY RUN COMPLETE] No database records were modified.");
            $this->info("To apply these corrections to returns and customer ledgers, run:");
            $this->comment("php artisan returns:fix-discount --execute");
            $this->info("------------------------------------------------------------------------");
            return 0;
        }

        // Execute corrections
        $this->info("Applying corrections to database and ledgers...");

        DB::beginTransaction();
        try {
            $balanceService = app(BalanceService::class);
            $journalService = app(JournalEntryService::class);
            $arId = $balanceService->getAccountsReceivableId();
            $salesRevenueId = $balanceService->getSalesRevenueId();

            $updatedCount = 0;

            foreach ($updatesToProcess as $batch) {
                /** @var SaleReturn $return */
                $return = $batch['return'];
                $newNetAmount = $batch['new_net_amount'];
                $newSubtotal = $batch['new_subtotal'];
                $newTotalItemDisc = $batch['new_total_item_disc'];

                // 1. Update return items
                foreach ($batch['items_data'] as $itemData) {
                    /** @var SaleReturnItem $item */
                    $item = $itemData['item'];
                    $item->update([
                        'item_discount' => $itemData['disc'],
                        'line_total' => $itemData['line_total'],
                    ]);
                }

                // 2. Update sale_return header
                $newBalance = max(0, $newNetAmount - (float)$return->paid);
                $return->update([
                    'bill_amount' => $newSubtotal,
                    'item_discount' => $newTotalItemDisc,
                    'net_amount' => $newNetAmount,
                    'balance' => $newBalance,
                ]);

                // 3. Update Journal Voucher & General Ledger if exists
                $voucher = VoucherMaster::where('voucher_type', VoucherMaster::TYPE_JOURNAL)
                    ->where(function ($q) use ($return) {
                        $q->where('remarks', 'LIKE', "%{$return->return_invoice}%")
                          ->orWhereHas('details', function ($dq) use ($return) {
                              $dq->where('narration', 'LIKE', "%{$return->return_invoice}%");
                          });
                    })
                    ->first();

                if ($voucher) {
                    // Reverse old journal entries to keep ledger accurate
                    $journalService->reverseEntriesForSource($voucher);

                    // Update voucher total
                    $voucher->total_amount = $newNetAmount;
                    $voucher->save();

                    // Update voucher details
                    foreach ($voucher->details as $d) {
                        if ($d->debit > 0) {
                            $d->debit = $newNetAmount;
                            if ($salesRevenueId) $d->account_id = $salesRevenueId;
                        }
                        if ($d->credit > 0) {
                            $d->credit = $newNetAmount;
                            if ($arId) $d->account_id = $arId;
                        }
                        $d->save();
                    }

                    // Re-post to Journal with updated amounts
                    foreach ($voucher->details as $detail) {
                        $includeParty = ($detail->account_id === $arId && $voucher->party_type);
                        $journalService->recordEntry(
                            $voucher,
                            $detail->account_id,
                            $detail->debit,
                            $detail->credit,
                            $detail->narration ?? $voucher->remarks,
                            $voucher->date ? $voucher->date->format('Y-m-d') : now()->format('Y-m-d'),
                            $includeParty ? $voucher->party : null
                        );
                    }
                }

                $updatedCount++;
            }

            DB::commit();

            $this->info("SUCCESS: Successfully corrected {$updatedCount} returns and their customer ledger vouchers!");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("ERROR: Correction failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
