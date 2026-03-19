<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'hospital_id',
        'categories',
        'name',
        'banner',
        'license_number',
        'address_id',
        'pincode',
        'latitude',
        'longitude',
        'contact_person',
        'mobile',
        'email',
        'opening_time',
        'closing_time',
        'sample_pickup_supported',
        'about',
        'is_verified',
        'is_active',
        'license',
        'gst_license',
        'store_front_photo',
        'owner_id_proof',
        'working_hours',
        'walk_in_sample_collection',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'categories' => 'array',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',

    ];

    public function scopeForHospital($query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    /**
     * user
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * address
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
