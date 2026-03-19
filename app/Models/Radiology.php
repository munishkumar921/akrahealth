<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Radiology extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'radiologies';

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
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
