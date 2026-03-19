<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReviewOfSystem extends Model
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
        'encounter_provider',
        'ros',
        'ros_gen',
        'ros_eye',
        'ros_ent',
        'ros_resp',
        'ros_cv',
        'ros_gi',
        'ros_gu',
        'ros_mus',
        'ros_neuro',
        'ros_psych',
        'ros_heme',
        'ros_endocrine',
        'ros_skin',
        'ros_wcc',
        'ros_psych1',
        'ros_psych2',
        'ros_psych3',
        'ros_psych4',
        'ros_psych5',
        'ros_psych6',
        'ros_psych7',
        'ros_psych8',
        'ros_psych9',
        'ros_psych10',
        'ros_psych11',
        'ros_date',
    ];
}
