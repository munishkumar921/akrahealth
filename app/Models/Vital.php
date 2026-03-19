<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vital extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'vitals';

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'vital_date',
        'age',
        'passage',
        'weight',
        'height',
        'head_circumference',
        'bmi',
        'temperature',
        'temperature_method',
        'bp_systolic',
        'bp_diastolic',
        'bp_position',
        'pulse',
        'respirations',
        'o2_saturation',
        'vitals_other',
        'wt_percentile',
        'ht_percentile',
        'hc_percentile',
        'wt_ht_percentile',
        'bmi_percentile',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'vital_date' => 'date:M d, Y',
        'age' => 'integer',
        'weight' => 'decimal:2',
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
}
