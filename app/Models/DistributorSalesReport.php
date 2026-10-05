<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorSalesReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function distributor()
    {
        return $this->belongsTo(Customer::class, 'distributor_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function items()
    {
        return $this->hasMany(DistributorSalesReportItem::class, 'report_id');
    }
}
