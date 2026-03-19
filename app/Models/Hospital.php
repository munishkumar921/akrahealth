<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'hospitals';

    protected $fillable = [
        'uuid',
        'name',
        'name_search',
        'registration_number',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'about',
        'latitude',
        'longitude',
        'is_active',
        'street_address1',
        'street_address2',
        'state_id',
        'country_id',
        'zip',
        'website',
        'primary_contact',
        'npi',
        'medicare',
        'tax_id',
        'default_pos_id',
        'documents_dir',
        'weight_unit',
        'height_unit',
        'temp_unit',
        'hc_unit',
        'encounter_template',
        'additional_message',
        'reminder_interval',
        'state',
        'country',
        'billing_street_address1',
        'billing_street_address2',
        'billing_city',
        'billing_state',
        'billing_zip',
        'phaxio_api_key',
        'phaxio_api_secret',
        'birthday_extension',
        'birthday_message',
        'appointment_extension',
        'appointment_interval',
        'appointment_message',
        'sms_url',
        'practice_logo',
        'main_branch_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * Get the timings for the hospital.
     */
    public function timings()
    {
        return $this->hasMany(HospitalTiming::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
    * Get the users for the hospital.
    */
}
