<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_return_id',
        'product_id',
        'is_manual',
        'product_name',
        'vendor_id',
        'purchase_price',
        'color',
        'warehouse_id',
        'qty',
        'boxes',
        'loose_pieces',
        'price',
        'item_discount',
        'unit',
        'line_total',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'boxes' => 'decimal:2',
        'price' => 'decimal:2',
        'item_discount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    // Relationships
    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            $return = $item->saleReturn;
            if ($return && $return->customer_id) {
                $customer = \App\Models\Customer::find($return->customer_id);
                if ($customer && $customer->customer_type == 'distributor') {
                    $stock = \App\Models\DistributorStock::where([
                        'distributor_id' => $customer->id,
                        'product_id' => $item->product_id
                    ])->first();
                    if ($stock) {
                        $stock->current_quantity -= $item->qty;
                        $stock->save();
                    }
                }
            }
        });

        static::deleted(function ($item) {
            $return = $item->saleReturn;
            if ($return && $return->customer_id) {
                $customer = \App\Models\Customer::find($return->customer_id);
                if ($customer && $customer->customer_type == 'distributor') {
                    $stock = \App\Models\DistributorStock::firstOrCreate([
                        'distributor_id' => $customer->id,
                        'product_id' => $item->product_id
                    ]);
                    $stock->current_quantity += $item->qty;
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
