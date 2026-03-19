<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'patient_id',
        'doctor_id',
        'lab_id',
        'pharmacy_id',
        'created_by',
        'updated_by',
        'appointment_type',
        'appointment_mode',
        'status',
        'appointment_date',
        'appointment_time',
        'duration_minutes',
        'reason',
        'notes',
        'is_follow_up',
        'parent_appointment_id',
        'recurring_type',
        'custom_recurring',
        'recurring_until',
        'payment_status',
        'payment_method',
        'fee_amount',
        'currency',
        'discount',
        'total_amount',
        'meeting_link',
        'timezone',
        'cancelled_reason',
        'rescheduled_at',
        'visit_type_id',
        'created_at',
        'updated_at',
        'razorpay_order_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function encounter()
    {
        return $this->hasOne(Encounter::class, 'appointment_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'appointment_id', 'id');
    }

    public function getAppointmentDateAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    public function getAppointmentTimeAttribute($value)
    {
        return Carbon::parse($value)->format('h:i A');
    }

    public function visitType()
    {
        return $this->belongsTo(VisitType::class, 'visit_type_id');
    }

    /**
     * hospital
     *
     * @var array
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

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
     * lab
     *
     * @return void
     */
    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id', 'id');
    }

    /**
     * pharmacy
     *
     * @return void
     */
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id', 'id');
    }

    /**
     * labOrders
     *
     * @return void
     */
    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    /**
     * prescription
     *
     * @return void
     */
    public function prescription()
    {
        return $this->hasOne(Prescription::class, 'appointment_id');
    }
}
