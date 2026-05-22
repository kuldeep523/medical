<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CamelCaseFields;

class Supplier extends Model
{
    use HasFactory, CamelCaseFields;

    protected $camelCaseAttributes = [
        'name',
        'address',
    ];

    protected $fillable = [
        'name',
        'mobile',
        'gst_number',
        'address',
        'current_balance',
        'user_id',
        'store_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('store', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->store_id) {
                $builder->where('store_id', auth()->user()->store_id);
            }
        });
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
