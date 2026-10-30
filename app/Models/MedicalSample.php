<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalSample extends Model
{
    use HasFactory;

    protected $table = 'medical_samples';

    protected $fillable = [
        'sample_no',
        'customer_id',
        'warehouse_id',
        'sample_date',
        'doctor_name',
        'mr_name',
        'total_pieces',
        'wos_amount',
        'remarks',
        'status',
        'created_by',
    ];

    protected $casts = [
        'sample_date' => 'date',
        'total_pieces' => 'float',
        'wos_amount' => 'float',
    ];

    public function items()
    {
        return $this->hasMany(MedicalSampleItem::class, 'medical_sample_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateSampleNo(): string
    {
        $lastId = self::max('id') ?? 0;
        do {
            $lastId++;
            $no = 'SMP-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        } while (self::where('sample_no', $no)->exists());
        return $no;
    }
}
