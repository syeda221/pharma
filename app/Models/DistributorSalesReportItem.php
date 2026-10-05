<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorSalesReportItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function report()
    {
        return $this->belongsTo(DistributorSalesReport::class, 'report_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
