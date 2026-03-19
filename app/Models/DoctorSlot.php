<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DoctorSlot extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
    ];
    // Note: start_time, end_time, and slot_duration are stored as TIME strings (H:i:s) in the database
    // and are not cast to avoid Laravel's unsupported 'time' cast type

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
