<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'sale_id',
        'medicine_id',
        'batch_no',
        'quantity',
        'refund_amount',
        'return_date',
        'remarks',
    ];

    protected static function booted()
    {
        static::addGlobalScope('store', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->store_id) {
                $builder->where('store_id', auth()->user()->store_id);
            }
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
