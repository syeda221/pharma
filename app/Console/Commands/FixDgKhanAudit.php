<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\VoucherMaster;
use App\Models\VoucherDetail;
use App\Models\JournalEntry;
use App\Models\CustomerLedger;
use App\Models\WarehouseStock;

class FixDgKhanAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:fix-dgkhan {--dry-run : Preview changes without committing to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile DG Khan September 2026 Audit (INV-0058 SIGPRO double entry and 3 Return dates)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info("======================================================");
        $this->info("DG KHAN SEPTEMBER 2026 AUDIT RECONCILIATION");
        $this->info("======================================================");
        if ($isDryRun) {
            $this->warn("DRY RUN MODE: No permanent changes will be saved.");
        }

        DB::beginTransaction();

        try {
            // 1. Locate INV-0058 (Sale 60)
            $sale = Sale::where('invoice_no', 'INV-0058')->first();
            if (!$sale) {
                $this->error("Invoice INV-0058 not found!");
                DB::rollBack();
                return 1;
            }

            // Find SIGPRO item in INV-0058
            $sigproItem = SaleItem::where('sale_id', $sale->id)
                ->where('product_id', 9)
                ->first();

            if ($sigproItem) {
                if ($sigproItem->qty == 12) {
                    $this->info("Found INV-0058 SIGPRO item #{$sigproItem->id}: reducing Qty 12 -> 7 pieces.");
                    $sigproItem->update([
                        'qty' => 7.0000,
                        'total_pieces' => 7.0000,
                        'discount_amount' => 386.75,
                        'total' => 3480.75,
                        'updated_at' => now(),
                    ]);

                    // Adjust sale totals
                    $sale->update([
                        'total_items' => 190.00,
                        'total_bill_amount' => 68000.85,
                        'total_net' => 68000.85,
                        'change' => -68000.85,
                        'updated_at' => now(),
                    ]);

                    // Adjust warehouse stock (+5 pieces)
                    DB::table('warehouse_stocks')
                        ->where('warehouse_id', 1)
                        ->where('product_id', 9)
                        ->increment('quantity', 5);
                    DB::table('warehouse_stocks')
                        ->where('warehouse_id', 1)
                        ->where('product_id', 9)
                        ->increment('total_pieces', 5);

                    DB::table('stock_movements')->where('id', 1051)->update(['qty' => -7.000, 'updated_at' => now()]);

                    // Update Voucher #216 & Journal Entries
                    $vm = VoucherMaster::where('id', 216)
                        ->orWhere('description', 'LIKE', '%INV-0058%')
                        ->first();
                    if ($vm) {
                        $vm->update(['total_amount' => 68000.85, 'updated_at' => now()]);
                        VoucherDetail::where('voucher_master_id', $vm->id)->where('debit', '>', 0)->update(['debit' => 68000.85]);
                        VoucherDetail::where('voucher_master_id', $vm->id)->where('credit', '>', 0)->update(['credit' => 68000.85]);
                        JournalEntry::where('source_type', VoucherMaster::class)->where('source_id', $vm->id)->where('debit', '>', 0)->update(['debit' => 68000.85]);
                        JournalEntry::where('source_type', VoucherMaster::class)->where('source_id', $vm->id)->where('credit', '>', 0)->update(['credit' => 68000.85]);
                    }

                    // Update CustomerLedger for Cust 17
                    CustomerLedger::where('id', 197)->update(['closing_balance' => 168445.35, 'updated_at' => now()]);
                    CustomerLedger::where('id', 284)->update(['previous_balance' => 168445.35, 'closing_balance' => 175261.50, 'updated_at' => now()]);
                } else {
                    $this->info("INV-0058 SIGPRO Qty is already {$sigproItem->qty} pieces.");
                }
            }

            // 2. Shift Return Dates to 30 September 2026
            $returnsMap = [
                'SR-0007' => ['date' => '2026-09-30 23:55:00', 'entry_date' => '2026-09-30', 'voucher_id' => 104, 'sm_id' => 485],
                'SR-0008' => ['date' => '2026-09-30 23:56:00', 'entry_date' => '2026-09-30', 'voucher_id' => 107, 'sm_id' => 490],
                'SR-0016' => ['date' => '2026-09-30 23:57:00', 'entry_date' => '2026-09-30', 'voucher_id' => 217, 'sm_id' => 1056],
            ];

            foreach ($returnsMap as $inv => $meta) {
                $sr = SaleReturn::where('return_invoice', $inv)->first();
                if ($sr) {
                    $this->info("Updating {$inv} date to 30-Sep-2026.");
                    $sr->update(['created_at' => $meta['date'], 'updated_at' => now()]);
                    
                    VoucherMaster::where('id', $meta['voucher_id'])->update([
                        'date' => $meta['entry_date'],
                        'created_at' => $meta['date'],
                        'updated_at' => now(),
                    ]);

                    JournalEntry::where('source_type', VoucherMaster::class)
                        ->where('source_id', $meta['voucher_id'])
                        ->update(['entry_date' => $meta['entry_date'], 'updated_at' => now()]);

                    DB::table('stock_movements')->where('id', $meta['sm_id'])->update(['created_at' => $meta['date'], 'updated_at' => now()]);
                }
            }

            if ($isDryRun) {
                DB::rollBack();
                $this->warn("Dry run completed. Rolled back successfully.");
            } else {
                DB::commit();
                $this->info("SUCCESS! DG Khan September Audit Reconciled Successfully.");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error occurred: " . $e->getMessage());
            return 1;
        }
    }
}
