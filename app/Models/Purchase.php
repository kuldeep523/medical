<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'bill_number',
        'bill_date',
        'total_amount',
        'paid_amount',
        'payment_mode',
        'user_id',
        'store_id',
<<<<<<< HEAD
        'dues_cleared_at',
=======
>>>>>>> a26ef6b30af880529baee2c9b637ce50b45c670f
    ];

    protected static function booted()
    {
        static::addGlobalScope('store', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->store_id) {
                $builder->where('store_id', auth()->user()->store_id);
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }
}
