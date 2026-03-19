<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PhysicalExamination extends Model
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
        'pe_date',
        'pe_gen1',
        'pe_eye1',
        'pe_eye2',
        'pe_eye3',
        'pe_ent1',
        'pe_ent2',
        'pe_ent3',
        'pe_ent4',
        'pe_ent5',
        'pe_ent6',
        'pe_neck1',
        'pe_neck2',
        'pe_resp1',
        'pe_resp2',
        'pe_resp3',
        'pe_resp4',
        'pe_cv1',
        'pe_cv2',
        'pe_cv3',
        'pe_cv4',
        'pe_cv5',
        'pe_cv6',
        'pe_ch1',
        'pe_ch2',
        'pe_gi1',
        'pe_gi2',
        'pe_gi3',
        'pe_gi4',
        'pe_gu1',
        'pe_gu2',
        'pe_gu3',
        'pe_gu4',
        'pe_gu5',
        'pe_gu6',
        'pe_gu7',
        'pe_gu8',
        'pe_gu9',
        'pe_lymph1',
        'pe_lymph2',
        'pe_lymph3',
        'pe_ms1',
        'pe_ms2',
        'pe_ms3',
        'pe_ms4',
        'pe_ms5',
        'pe_ms6',
        'pe_ms7',
        'pe_ms8',
        'pe_ms9',
        'pe_ms10',
        'pe_ms11',
        'pe_ms12',
        'pe_skin1',
        'pe_skin2',
        'pe_neuro1',
        'pe_neuro2',
        'pe_neuro3',
        'pe_psych1',
        'pe_psych2',
        'pe_psych3',
        'pe_psych4',
        'pe',
        'pe_constitutional1',
        'pe_mental1',
    ];
}
