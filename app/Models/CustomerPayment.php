<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    protected $fillable = [
        'customer_id',
        'admin_or_user_id',
        'amount',
        'payment_method',
        'payment_date',
        'note',
        'batch_no',
        'receipt_no',
        'account_id',
        'cheque_no',
        'cheque_date',
        'attachment',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cheque_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_or_user_id');
    }

    /**
     * Generate unique Receipt Number for each customer payment entry
     */
    public static function generateReceiptNo()
    {
        $prefix = 'RCP-' . date('Ymd') . '-';
        $latest = self::where('receipt_no', 'like', $prefix . '%')->latest('id')->first();
        if ($latest && preg_match('/-(\d+)$/', $latest->receipt_no, $m)) {
            $seq = intval($m[1]) + 1;
        } else {
            $seq = 1;
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique Batch Recovery Number for a bulk session
     */
    public static function generateBatchNo()
    {
        $prefix = 'BAT-REC-' . date('Ymd') . '-';
        $latest = self::where('batch_no', 'like', $prefix . '%')->latest('id')->first();
        if ($latest && preg_match('/-(\d+)$/', $latest->batch_no, $m)) {
            $seq = intval($m[1]) + 1;
        } else {
            $seq = 1;
        }
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
