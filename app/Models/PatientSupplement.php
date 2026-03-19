<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientSupplement extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'patient_supplements';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'date_active',
        'date_inactive',
        'date_prescribed',
        'supplement',
        'dosage',
        'dosage_unit',
        'sig',
        'route',
        'frequency',
        'instructions',
        'quantity',
        'reason',
        'reconcile',
    ];

    protected $casts = [
        'date_active' => 'datetime:M d, Y',
        'date_inactive' => 'datetime:M d, Y',
        'date_prescribed' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];
}
