<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_id', 'customer_name', 'customer_name_ur', 'cnic', 'filer_type', 'zone',
        'contact_person', 'mobile', 'email_address', 'contact_person_2', 'mobile_2',
        'email_address_2', 'opening_balance', 'balance_range', 'address', 'status',
        'customer_type', 'custom_discount_percentage', 'custom_discount_medical',
        'custom_discount_doctor', 'custom_discount_distribution',
        'previous_balance', 'sales_officer_id',
        'payment_reminder_date', 'reminder_snoozed_at', 'reminder_day', 'source'
    ];

    protected $appends = ['discount_percentage', 'discount_tiers'];

    public function customerTypeModel()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type', 'name');
    }

    /**
     * Get effective 3-tier discounts for customer
     */
    public function getDiscountTiersAttribute()
    {
        $typeModel = $this->customerTypeModel;
        
        $med = !is_null($this->custom_discount_medical) 
            ? (float)$this->custom_discount_medical 
            : (float)($typeModel->discount_medical ?? 0);

        $doc = !is_null($this->custom_discount_doctor) 
            ? (float)$this->custom_discount_doctor 
            : (float)($typeModel->discount_doctor ?? ($typeModel->discount_percentage ?? 0));

        $dist = !is_null($this->custom_discount_distribution) 
            ? (float)$this->custom_discount_distribution 
            : (float)($typeModel->discount_distribution ?? 0);

        // Fallback if legacy discount_percentage is set but tiers are 0
        if ($med == 0 && $doc == 0 && $dist == 0 && $this->discount_percentage > 0) {
            $doc = (float) $this->discount_percentage;
        }

        return [
            'medical' => $med,
            'doctor' => $doc,
            'distribution' => $dist,
        ];
    }

    /**
     * Get effective discount percentage for customer
     */
    public function getDiscountPercentageAttribute()
    {
        if (!is_null($this->custom_discount_percentage)) {
            return (float) $this->custom_discount_percentage;
        }

        if (!empty($this->customer_type)) {
            $typeModel = $this->customerTypeModel;
            if ($typeModel) {
                return (float) ($typeModel->discount_doctor > 0 ? $typeModel->discount_doctor : ($typeModel->discount_percentage ?? 0));
            }
        }

        return 0.0;
    }

    public function salesOfficer()
    {
        return $this->belongsTo(SalesOfficer::class, 'sales_officer_id');
    }

    /**
     * Polymorphic relationship to journal entries
     */
    public function journalEntries()
    {
        return $this->morphMany(JournalEntry::class, 'party');
    }

    /**
     * Get current balance from BalanceService
     */
    public function getPreviousBalanceAttribute()
    {
        // Use BalanceService to calculate the real-time balance
        // including opening balance and journal entries.
        try {
            $balanceService = app(\App\Services\BalanceService::class);
            return $balanceService->getCustomerBalance($this);
        } catch (\Exception $e) {
            // Fallback to column if service fails
            return $this->attributes['previous_balance'] ?? 0;
        }
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (!isset($model->is_synced)) {
                $model->is_synced = 0;
            }
        });
    }
}
