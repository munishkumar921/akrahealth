<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
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
        'encounter_id',
        'patient_id',
        'doctor_id',
        'encounter_provider',
        'assessment_date',
        'assessment_other',
        'assessment_ddx',
        'assessment_notes',
        'assessment_1',
        'assessment_2',
        'assessment_3',
        'assessment_4',
        'assessment_5',
        'assessment_6',
        'assessment_7',
        'assessment_8',
        'assessment_9',
        'assessment_10',
        'assessment_11',
        'assessment_12',
        'assessment_icd1',
        'assessment_icd2',
        'assessment_icd3',
        'assessment_icd4',
        'assessment_icd5',
        'assessment_icd6',
        'assessment_icd7',
        'assessment_icd8',
        'assessment_icd9',
        'assessment_icd10',
        'assessment_icd11',
        'assessment_icd12',
        'other',
        'differential_diagnoses',
        'assessment_discussion',
    ];

    protected $casts = [
        'assessment_date' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    /**
     * encounter
     *
     * @return void
     */
    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
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
     * patient
     *
     * @return void
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
