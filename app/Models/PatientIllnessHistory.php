<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PatientIllnessHistory extends Model
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
        'patient_id',
        'hpi',
        'forms',
        'situation',
        'date',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'date' => 'date:M d, Y',
    ];
}
