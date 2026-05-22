<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CamelCaseFields;

class Medicine extends Model
{
    use HasFactory, CamelCaseFields;

    protected $camelCaseAttributes = [
        'name',
        'rx_salt',
        'purpose',
        'brand_name',
        'location_section',
        'location_column',
    ];

    protected $fillable = [
        'name',
        'image',
        'rx_salt',
        'purpose',
        'power_mg',
        'units_per_strip',
        'brand_name',
        'reorder_point',
        'location_section',
        'location_column',
        'user_id',
        'store_id',
    ];

    /**
     * Get the total stock by summing all batch quantities.
     */
    public function getTotalStockAttribute()
    {
        return $this->batches()->sum('quantity');
    }

    /**
     * Check if the medicine is sold out.
     */
    public function getIsSoldOutAttribute()
    {
        return $this->total_stock <= 0;
    }

    /**
     * Get formatted stock (Strips and Units).
     */
    public function getFormattedStockAttribute()
    {
        if ($this->units_per_strip <= 1) {
            return $this->total_stock . ' units';
        }

        $strips = floor($this->total_stock / $this->units_per_strip);
        $units = $this->total_stock % $this->units_per_strip;

        return $strips . ' strips, ' . $units . ' tablets';
    }

    protected static function booted()
    {
        static::addGlobalScope('store', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->store_id) {
                $builder->where('store_id', auth()->user()->store_id);
            }
        });
    }

    /**
     * Get the user that owns the medicine.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the batches for the medicine.
     */
    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }
}
