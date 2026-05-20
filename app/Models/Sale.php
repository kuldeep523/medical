<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bill_no',
        'total_amount',
        'store_id',
        'customer_name',
        'customer_phone',
        'payment_method',
        'amount_paid',
        'order_type',
        'dispatch_status',
        'bill_tag',
        'patient_id',
        'patient_name',
        'patient_address',
        'patient_reg_no',
        'doctor_name',
        'doctor_number',
        'dues_cleared_at',
    ];

    protected static function booted()
    {
        static::addGlobalScope('store', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->store_id) {
                $builder->where('store_id', auth()->user()->store_id);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
