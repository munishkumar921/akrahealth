<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
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
        'detail',
        'code',
        'specialty',
        'doctor_id',
        'pending_date',
        'insurance_id',
        'note',
    ];

    /**
     * doctor
     *
     * @return void
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
