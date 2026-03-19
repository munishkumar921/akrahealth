<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DoctorPatient extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'doctor_patient';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'patient_id',
        'accept_term_condition',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-m-Y',
        'updated_at' => 'datetime:d-m-Y',

    ];

    protected $dates = ['created_at', 'updated_at'];

    public function getCreatedAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    // 🔹 Relation to Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    // 🔹 Relation to Doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
