<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'medications';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'patient_id',
        'doctor_id',
        'encounter_id',
        'medication',
        'dosage',
        'dosage_unit',
        'sig',
        'route',
        'frequency',
        'instructions',
        'reason',
        'date_active',
        'date_inactive',
        'rcopia_sync',
        'reconcile',
        'quantity',
        'refill',
        'prescription',
        'due_date',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',

    ];

    public function getDateActiveAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    public function getDateInactiveAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
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
}
