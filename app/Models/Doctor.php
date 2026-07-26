<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\CamelCaseFields;

class Doctor extends Model
{
    use HasFactory, CamelCaseFields;

    protected $camelCaseAttributes = [
        'name',
        'specialization',
        'clinic_name',
        'clinic_address',
    ];

    protected $fillable = [
        'store_id',
        'name',
        'specialization',
        'phone',
        'email',
        'clinic_name',
        'clinic_address',
        'registration_no',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    public function sales()
    {
        return $this->hasMany(Sale::class, 'doctor_name', 'name');
    }
}
