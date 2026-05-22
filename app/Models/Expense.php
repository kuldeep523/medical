<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CamelCaseFields;

class Expense extends Model
{
    use HasFactory, CamelCaseFields;

    protected $camelCaseAttributes = [
        'category',
        'description',
    ];

    protected $fillable = [
        'store_id',
        'expense_date',
        'category',
        'amount',
        'description',
        'payment_method',
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
}
