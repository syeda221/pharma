<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalSampleItem extends Model
{
    use HasFactory;

    protected $table = 'medical_sample_items';

    protected $fillable = [
        'medical_sample_id',
        'product_id',
        'variant_color',
        'qty',
        'purchase_price',
        'retail_price',
        'wos_line_total',
    ];

    protected $casts = [
        'qty' => 'float',
        'purchase_price' => 'float',
        'retail_price' => 'float',
        'wos_line_total' => 'float',
    ];

    public function sample()
    {
        return $this->belongsTo(MedicalSample::class, 'medical_sample_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
