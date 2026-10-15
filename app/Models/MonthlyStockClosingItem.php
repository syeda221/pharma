<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyStockClosingItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function closing()
    {
        return $this->belongsTo(MonthlyStockClosing::class, 'closing_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
