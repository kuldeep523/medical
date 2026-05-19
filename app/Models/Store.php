<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'owner_name',
        'email',
        'address',
        'gst_number',
        'status',
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
