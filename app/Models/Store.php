<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CamelCaseFields;

class Store extends Model
{
    use HasFactory, CamelCaseFields;

    protected $camelCaseAttributes = [
        'store_name',
        'owner_name',
        'address',
    ];

    protected $fillable = [
        'store_name',
        'owner_name',
        'email',
        'address',
        'gst_number',
        'status',
        'plan_name',
        'plan_expired_at',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
