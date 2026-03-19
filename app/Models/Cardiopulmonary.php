<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cardiopulmonary extends Model
{
    protected $table = 'cardiopulmonary';

    protected $fillable = [
        'name',
        'address_id',
        'hospital_id',
        'doctor_id',
        'facility_type',
        'created_by',
        'website',
        'is_verified',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
