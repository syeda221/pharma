<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorFundTransaction extends Model
{
    use HasFactory;

    protected $table = 'doctor_fund_transactions';

    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship to Zone (Distributor)
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    /**
     * Relationship to Customer (Doctor)
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relationship to Bank / Financial Account
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Relationship to User who created this entry
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if attachment is a PDF
     */
    public function getIsPdfAttribute(): bool
    {
        if (!$this->attachment) {
            return false;
        }
        $ext = strtolower(pathinfo($this->attachment, PATHINFO_EXTENSION));
        return $ext === 'pdf' || $this->attachment_mime === 'application/pdf';
    }

    /**
     * Check if attachment is an Image
     */
    public function getIsImageAttribute(): bool
    {
        if (!$this->attachment) {
            return false;
        }
        $ext = strtolower(pathinfo($this->attachment, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
    }

    /**
     * Full URL of the attachment
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment) {
            return null;
        }
        return asset('storage/' . $this->attachment);
    }
}
