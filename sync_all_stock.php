<?php
require __DIR__.'/vendor/autoload.php';
\ = require_once __DIR__.'/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

\App\Models\DistributorStock::truncate();

// 1. Add Sales
\ = \App\Models\SaleItem::with('sale')->get();
foreach(\ as \) {
    \ = \->sale;
    if (\ && \->customer_id) {
        \ = \App\Models\DistributorStock::firstOrCreate([
            'distributor_id' => \->customer_id,
            'product_id' => \->product_id
        ], ['current_quantity' => 0]);
        \->current_quantity += \->total_pieces;
        \->save();
    }
}

// 2. Subtract Returns
\ = \App\Models\SaleReturnItem::with('saleReturn')->get();
foreach(\ as \) {
    \ = \->saleReturn;
    if (\ && \->customer_id) {
        \ = \App\Models\DistributorStock::where('distributor_id', \->customer_id)
            ->where('product_id', \->product_id)->first();
        if (\) {
            \->current_quantity -= \->total_pieces;
            \->save();
        }
    }
}

// 3. Subtract Submitted Reports
\ = \App\Models\DistributorSalesReportItem::with('report')->get();
foreach(\ as \) {
    \ = \->report;
    if (\ && \->distributor_id) {
        \ = \App\Models\DistributorStock::where('distributor_id', \->distributor_id)
            ->where('product_id', \->product_id)->first();
        if (\) {
            \->current_quantity -= \->sold_quantity;
            \->save();
        }
    }
}
echo 'Done';
?>
