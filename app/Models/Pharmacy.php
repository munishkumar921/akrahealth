<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'hospital_id',
        'country',
        'state',
        'name',
        'banner',
        'license_number',
        'address',
        'city',
        'pincode',
        'latitude',
        'longitude',
        'contact_person',
        'mobile',
        'email',
        'opening_time',
        'closing_time',
        'about',
        'is_verified',
        'is_active',
        'license',
        'gst_license',
        'store_front_photo',
        'owner_id_proof',
        'working_hours',
        'zokto_delivery',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * orders
     *
     * @return void
     */
    // public function orders()
    // {
    //     return $this->hasMany(PharmacyOrder::class);
    // }

    /**
     * user
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
