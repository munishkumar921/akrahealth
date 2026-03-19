<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encounter extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'encounters';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'chief_complaint',
        'encounter_date',
        'encounter_signed',
        'date_signed',
        'encounter_date_of_service',
        'encounter_age',
        'encounter_type',
        'encounter_location',
        'encounter_activity',
        'complexity_of_encounter',
        'encounter_cc',
        'activity',
        'cc',
        'encounter_condition',
        'encounter_condition_work',
        'encounter_condition_auto',
        'encounter_condition_auto_state',
        'encounter_condition_other',
        'encounter_role',
        'referring_provider',
        'bill_submitted',
        'addendum',
        'addendum_eid',
        'provider_role',
        'referring_provider_npi',
        'encounter_template',
        'bill_complex',
        'appointment_id',
    ];

    protected $casts = [
        'encounter_date' => 'datetime:M d, Y',
        'date_signed' => 'datetime:M d, Y',
        'encounter_date_of_service' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * patient
     *
     * @return void
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * doctor
     *
     * @return void
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * appointment
     *
     * @return void
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * patientIllnessHistory
     *
     * @return void
     */
    public function patientIllnessHistory()
    {
        return $this->hasOne(PatientIllnessHistory::class, 'encounter_id', 'id');
    }

    /**
     * reviewOfSystem
     *
     * @return void
     */
    public function reviewOfSystem()
    {
        return $this->hasOne(ReviewOfSystem::class, 'encounter_id', 'id');
    }

    /**
     * vital
     *
     * @return void
     */
    public function vital()
    {
        return $this->hasOne(Vital::class, 'encounter_id', 'id');
    }

    /**
     * assessment
     *
     * @return void
     */
    public function assessment()
    {
        return $this->hasOne(Assessment::class, 'encounter_id', 'id');
    }

    /**
     * plan
     *
     * @return void
     */
    public function plan()
    {
        return $this->hasOne(Plan::class, 'encounter_id', 'id');
    }

    /**
     * physicalExamination
     *
     * @return void
     */
    public function physicalExamination()
    {
        return $this->hasOne(PhysicalExamination::class, 'encounter_id', 'id');
    }

    /**
     * prescriptions
     *
     * @return void
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'encounter_id', 'id');
    }

    /**
     * supplements
     *
     * @return void
     */
    public function supplements()
    {
        return $this->hasMany(PatientSupplement::class, 'encounter_id', 'id');
    }

    /**
     * labOrders
     *
     * @return void
     */
    public function labOrders()
    {
        return $this->hasMany(Order::class, 'encounter_id', 'id')->whereNotNull('labs');
    }

    /**
     * radiologyOrders
     *
     * @return void
     */
    public function radiologyOrders()
    {
        return $this->hasMany(Order::class, 'encounter_id', 'id')->whereNotNull('radiology');
    }

    /**
     * cardOrders
     *
     * @return void
     */
    public function cardOrders()
    {
        return $this->hasMany(Order::class, 'encounter_id', 'id')->whereNotNull('cp');
    }

    /**
     * images
     *
     * @return void
     */
    public function images()
    {
        return $this->hasMany(Image::class, 'encounter_id', 'id')->where('type', 'anatomical');
    }

    /**
     * photos
     *
     * @return void
     */
    public function photos()
    {
        return $this->hasMany(Image::class, 'encounter_id', 'id')->where('type', 'photo');
    }

    /**
     * procedures
     *
     * @return void
     */
    public function procedures()
    {
        return $this->hasMany(Procedure::class, 'encounter_id', 'id');
    }

    /**
     * billingCore
     *
     * @return void
     */
    public function billingCore()
    {
        return $this->hasOne(BillingCore::class, 'encounter_id', 'id');
    }

    /**
     * billing
     *
     * @return void
     */
    public function billing()
    {
        return $this->hasOne(Billing::class, 'encounter_id', 'id');
    }

    /**
     * referral
     *
     * @return void
     */
    public function referral()
    {
        return $this->hasOne(Referral::class, 'encounter_id', 'id');
    }
}
