<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'lab_id',
        'encounter_provider',
        'orders_date',
        'insurance_id',
        'referrals',
        'labs',
        'radiology',
        'cp',
        'referrals_icd',
        'labs_icd',
        'radiology_icd',
        'cp_icd',
        'labs_obtained',
        'notes',
        'pending_date',
        'is_completed',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'doctors',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'pending_date' => 'date:M d, Y',
        'orders_date' => 'date:M d, Y',
        'is_completed' => 'boolean',
    ];

    public function getDoctorsAttribute()
    {
        return $this->doctor()->with('user')->first();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'encounter_provider');
    }

    public function encounterProvider()
    {
        return $this->belongsTo(Lab::class, 'encounter_provider');
    }

    /**
     * labOrders
     * Relationship to lab_orders table
     *
     * @return void
     */
    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id');
    }
}
