<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Customer;
use App\Services\BalanceService;
use App\Http\Controllers\SaleController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteReturnedSalesInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:delete-returned-invoices 
                            {--dry-run : Preview which sales invoices will be deleted} 
                            {--execute : Perform clean deletion of non-DG Khan returned sales invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete non-DG Khan sales invoices that had returns, restoring unreturned stock and cleaning ledgers';

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

        $saleIds = [1, 5, 22, 23, 31, 38, 46, 51, 97, 98, 99, 102, 111, 113, 115, 117, 120];

        $sales = Sale::with(['items.product', 'customer_relation'])
            ->whereIn('id', $saleIds)
            ->orderBy('id', 'asc')
            ->get();

        if ($sales->isEmpty()) {
            $this->info("No returned sales invoices found to delete.");
            return 0;
        }

        $this->info("==================================================================");
        $this->info(" AUDITING RETURNED SALES INVOICES (NON-DG KHAN)");
        $this->info("==================================================================");

        $tableData = [];
        foreach ($sales as $s) {
            $cust = $s->customer_relation;
            $soldPieces = $s->items->sum('total_pieces');
            $tableData[] = [
                'ID' => $s->id,
                'Invoice #' => $s->invoice_no,
                'Date' => $s->created_at->format('Y-m-d'),
                'Customer' => $cust->customer_name ?? 'N/A',
                'Zone' => $cust->zone ?? 'N/A',
                'Net Amount' => number_format($s->total_net, 2),
                'Sold Pieces' => $soldPieces,
                'Status' => $s->sale_status,
            ];
        }

        $this->table(['ID', 'Invoice #', 'Date', 'Customer', 'Zone', 'Net Amount', 'Sold Pieces', 'Status'], $tableData);

        if ($isDryRun) {
            $this->warn("\n[DRY-RUN] No invoices were deleted. Pass --execute to delete these " . $sales->count() . " sales invoices and clean up stock/ledgers.");
            return 0;
        }

        $this->warn("\n[EXECUTE] Proceeding with atomic deletion of " . $sales->count() . " sales invoices inside a DB Transaction...");

        DB::beginTransaction();

        try {
            $controller = app(SaleController::class);
            $deletedCount = 0;

            foreach ($sales as $s) {
                $sid = $s->id;
                $inv = $s->invoice_no;
                $controller->destroy($sid);
                $deletedCount++;
                $this->info("- Deleted Sale #{$sid} ({$inv})");
            }

            DB::commit();

            $this->info("==================================================================");
            $this->info(" DELETION COMPLETED SUCCESSFULLY!");
            $this->info("==================================================================");
            $this->info("- Sales Invoices Deleted: " . $deletedCount);
            $this->info("- Remaining Sales in Database: " . Sale::count());
            $this->info("- DG Khan Returns Remaining: " . SaleReturn::count());

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("FAILED with error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
