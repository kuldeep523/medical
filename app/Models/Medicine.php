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
    ];

    protected $fillable = [
        'name',
        'image',
        'rx_salt',
        'purpose',
        'power_mg',
        'brand_name',
        'reorder_point',
        'gst_percent',
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
        // Simple display if strips vary per batch, just show total units
        return $this->total_stock . ' units';
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
