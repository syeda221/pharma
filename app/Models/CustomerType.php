<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'discount_percentage',
        'discount_medical',
        'discount_doctor',
        'discount_distribution',
        'is_static'
    ];

    protected $casts = [
        'is_static' => 'boolean',
        'discount_percentage' => 'float',
        'discount_medical' => 'float',
        'discount_doctor' => 'float',
        'discount_distribution' => 'float',
    ];

    protected $appends = ['discount_tiers'];

    public function getDiscountTiersAttribute()
    {
        return [
            'medical' => (float) ($this->discount_medical ?? 0),
            'doctor' => (float) ($this->discount_doctor ?? ($this->discount_percentage ?? 0)),
            'distribution' => (float) ($this->discount_distribution ?? 0),
        ];
    }
}
