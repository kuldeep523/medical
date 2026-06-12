<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CamelCaseFields;

class MedicineBatch extends Model
{
    use HasFactory, CamelCaseFields;

    protected $camelCaseAttributes = [
        'vendor_name',
        'location_section',
        'location_column',
    ];

    protected $fillable = [
        'medicine_id',
        'batch_no',
        'expiry_date',
        'quantity',
        'purchase_price',
        'sales_price',
        'units_per_strip',
        'user_id',
        'store_id',
        'vendor_bill_path',
        'vendor_name',
        'amount_paid_to_vendor',
        'return_status',
        'reorder_point',
        'purchase_id',
        'location_section',
        'location_column',
    ];

    protected static function booted()
    {
        static::addGlobalScope('store', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->store_id) {
                $builder->where('store_id', auth()->user()->store_id);
            }
        });
    }

    /**
     * Get the purchase that brought in this batch.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the medicine that owns the batch.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
