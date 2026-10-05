<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'warehouse_id', 'product_id', 
        'brand_id', 'category_id', 'sub_category_id', 'unit_id',
        'qty', 'price', 'total',
        'discount_percent', 'discount_amount',
        'color', 'total_pieces', 'loose_pieces',
        'price_per_piece', 'price_per_m2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            $sale = $item->sale;
            if ($sale && $sale->customer_relation ) {
                $stock = \App\Models\DistributorStock::firstOrCreate([
                    'distributor_id' => $sale->customer_id,
                    'product_id' => $item->product_id
                ]);
                $stock->current_quantity += $item->total_pieces;
                $stock->save();
            }
        });

        static::deleted(function ($item) {
            $sale = $item->sale;
            if ($sale && $sale->customer_relation ) {
                $stock = \App\Models\DistributorStock::where([
                    'distributor_id' => $sale->customer_id,
                    'product_id' => $item->product_id
                ])->first();
                if ($stock) {
                    $stock->current_quantity -= $item->total_pieces;
                    $stock->save();
                }
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
