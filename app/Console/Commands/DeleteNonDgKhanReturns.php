<?php

namespace App\Console\Commands;

use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\WarehouseStock;
use App\Models\VoucherMaster;
use App\Models\VoucherDetail;
use App\Models\JournalEntry;
use App\Models\CustomerLedger;
use App\Services\BalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteNonDgKhanReturns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'returns:delete-non-dgkhan 
                            {--dry-run : Preview which returns will be deleted without touching database} 
                            {--execute : Perform clean deletion of non-DG Khan returns}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely delete all non-DG Khan sale returns and restore stock, ledgers, vouchers, and sale status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isExecute = $this->option('execute');

        if (!$isDryRun && !$isExecute) {
            $isDryRun = true;
            $this->info("No flag provided. Running in [DRY-RUN] preview mode by default.");
            $this->info("Use --execute to actually apply changes.\n");
        }

        $this->info("==================================================================");
        $this->info(" AUDITING ALL SALE RETURNS (PRESERVING DG KHAN ZONE 3)");
        $this->info("==================================================================");

        $allReturns = SaleReturn::with(['items.product', 'sale', 'customer'])->orderBy('id', 'asc')->get();

        if ($allReturns->isEmpty()) {
            $this->info("No sale returns found in the system.");
            return 0;
        }

        $keepList = [];
        $deleteList = [];

        foreach ($allReturns as $ret) {
            $cust = $ret->customer;
            $isDgKhan = false;

            if ($cust) {
                $custZone = (string)($cust->zone ?? '');
                $custName = (string)($cust->customer_name ?? '');
                if ($custZone === '3' || stripos($custZone, 'DG KHAN') !== false || stripos($custName, 'DG KHAN') !== false) {
                    $isDgKhan = true;
                }
            }
            if (in_array($ret->id, [3, 7, 8, 12, 16])) {
                $isDgKhan = true;
            }

            if ($isDgKhan) {
                $keepList[] = $ret;
            } else {
                $deleteList[] = $ret;
            }
        }

        $this->info(sprintf("Total Returns: %d | To KEEP (DG Khan): %d | To DELETE (Others): %d\n", 
            $allReturns->count(), 
            count($keepList), 
            count($deleteList)
        ));

        $this->info("--- [DG KHAN RETURNS TO KEEP] ---");
        $keepRows = [];
        foreach ($keepList as $k) {
            $keepRows[] = [
                'ID' => $k->id,
                'Invoice' => $k->return_invoice,
                'Date' => $k->return_date,
                'Customer' => $k->customer->customer_name ?? 'N/A',
                'Zone' => $k->customer->zone ?? 'N/A',
                'Net Amount' => number_format($k->net_amount, 2),
            ];
        }
        $this->table(['ID', 'Invoice', 'Date', 'Customer', 'Zone', 'Net Amount'], $keepRows);

        $this->info("\n--- [NON-DG KHAN RETURNS TO DELETE] ---");
        $delRows = [];
        foreach ($deleteList as $d) {
            $delRows[] = [
                'ID' => $d->id,
                'Invoice' => $d->return_invoice,
                'Date' => $d->return_date,
                'Customer' => $d->customer->customer_name ?? 'N/A',
                'Zone' => $d->customer->zone ?? 'N/A',
                'Sale Inv' => $d->sale->invoice_no ?? ('Sale #' . ($d->sale_id ?? 'N/A')),
                'Net Amount' => number_format($d->net_amount, 2),
            ];
        }
        $this->table(['ID', 'Invoice', 'Date', 'Customer', 'Zone', 'Sale Inv', 'Net Amount'], $delRows);

        if ($isDryRun) {
            $this->warn("\n[DRY-RUN] No changes were made. Pass --execute to permanently delete the " . count($deleteList) . " returns and clean up stock/ledgers.");
            return 0;
        }

        // EXECUTION MODE
        $this->warn("\n[EXECUTE] Proceeding with atomic deletion of " . count($deleteList) . " returns inside a DB Transaction...");

        DB::beginTransaction();

        try {
            $affectedCustomerIds = [];
            $affectedSaleIds = [];
            $deletedItemsCount = 0;
            $revertedStockPieces = 0;
            $deletedVouchersCount = 0;
            $deletedJournalEntriesCount = 0;

            foreach ($deleteList as $ret) {
                $returnId = $ret->id;
                $returnInvoice = $ret->return_invoice;
                $customerId = $ret->customer_id;
                $saleId = $ret->sale_id;

                if ($customerId) {
                    $affectedCustomerIds[$customerId] = true;
                }
                if ($saleId) {
                    $affectedSaleIds[$saleId] = true;
                }

                // 1. Revert warehouse stocks (decrement returned pieces)
                foreach ($ret->items as $item) {
                    $stock = WarehouseStock::where('warehouse_id', $item->warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->first();
                    if ($stock) {
                        $ppb = (int)($item->product->pieces_per_box ?? 1);
                        if ($ppb <= 0) $ppb = 1;

                        $stock->total_pieces = $stock->total_pieces - (float)$item->qty;
                        $stock->quantity = $stock->total_pieces / $ppb;
                        $stock->save();

                        $revertedStockPieces += (float)$item->qty;
                    }
                    $deletedItemsCount++;
                }

                // 2. Delete stock movements
                DB::table('stock_movements')
                    ->where('ref_type', 'SALE_RETURN')
                    ->where('ref_id', $returnId)
                    ->delete();

                // 3. Delete vouchers & journal entries
                $vouchers = VoucherMaster::where('remarks', 'LIKE', "%{$returnInvoice}%")->get();
                foreach ($vouchers as $vm) {
                    $jeCount = JournalEntry::where('source_type', VoucherMaster::class)
                        ->where('source_id', $vm->id)
                        ->delete();
                    $deletedJournalEntriesCount += $jeCount;

                    VoucherDetail::where('voucher_master_id', $vm->id)->delete();
                    $vm->delete();
                    $deletedVouchersCount++;
                }

                // 4. Delete CustomerLedger entries
                if ($customerId) {
                    CustomerLedger::where('customer_id', $customerId)
                        ->where('description', 'LIKE', "%{$returnInvoice}%")
                        ->delete();
                }

                // 5. Delete items and return record
                $ret->items()->delete();
                $ret->delete();
            }

            // 6. Restore Sale Status for affected sales
            $keepReturnIds = array_map(fn($r) => $r->id, $keepList);
            $restoredSalesCount = 0;
            foreach (array_keys($affectedSaleIds) as $sid) {
                $sale = Sale::find($sid);
                if ($sale) {
                    // Check if any returns still exist for this sale
                    $hasRemainingReturns = SaleReturn::where('sale_id', $sale->id)
                        ->whereIn('id', $keepReturnIds)
                        ->exists();

                    if (!$hasRemainingReturns && $sale->sale_status === 'returned') {
                        $sale->update(['sale_status' => 'posted']);
                        $restoredSalesCount++;
                    }
                }
            }

            // 7. Re-sync Customer previous_balance via BalanceService
            $resyncedCustomersCount = 0;
            $balanceService = app(BalanceService::class);
            foreach (array_keys($affectedCustomerIds) as $cid) {
                $cust = Customer::find($cid);
                if ($cust) {
                    $newBalance = $balanceService->getCustomerBalance($cid);
                    $cust->previous_balance = $newBalance;
                    $cust->save();
                    $resyncedCustomersCount++;
                }
            }

            DB::commit();

            $this->info("==================================================================");
            $this->info(" DELETION COMPLETED SUCCESSFULLY!");
            $this->info("==================================================================");
            $this->info("- Sale Returns Deleted: " . count($deleteList));
            $this->info("- Return Items Deleted: " . $deletedItemsCount);
            $this->info("- Stock Pieces Reverted: " . $revertedStockPieces);
            $this->info("- Vouchers Deleted: " . $deletedVouchersCount);
            $this->info("- Journal Entries Deleted: " . $deletedJournalEntriesCount);
            $this->info("- Sales Restored to 'posted': " . $restoredSalesCount);
            $this->info("- Customer Balances Re-synced: " . $resyncedCustomersCount);
            $this->info("- DG Khan Returns Remaining: " . count($keepList));

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("FAILED with error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
