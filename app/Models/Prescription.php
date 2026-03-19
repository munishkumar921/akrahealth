<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
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
        'encounter_id',
        'doctor_id',
        'patient_id',
        'rx',
        'supplements',
        'immunizations',
        'orders_summary',
        'supplements_orders_summary',
        'prescription',
        'medication',
        'dosage',
        'dosage_unit',
        'sig',
        'route',
        'frequency',
        'instructions',
        'quantity',
        'refill',
        'reason',
        'date_inactive',
        'date_active',
        'pharmacy_id',
        'date_old',
        'provider',
        'dea',
        'daw',
        'license',
        'days',
        'due_date',
        'rcopia_sync',
        'national_drug_code',
        'reconcile',
        'json',
        'transaction',
    ];

    protected $casts = [
        'rx' => 'array',
        'supplements' => 'array',
        'immunizations' => 'array',
        'orders_summary' => 'array',
        'supplements_orders_summary' => 'array',
        'json' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_inactive',
        'date_active',
        'date_old',
        'due_date',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function getDateActiveAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->format('M d, Y');
    }

    public function getDateInactiveAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->format('M d, Y');
    }

    public function getDueDateAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->format('M d, Y');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }
}
