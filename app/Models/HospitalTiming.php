<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HospitalTiming extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'hospital_id',
        'day_of_week',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'morning_slot',
        'afternoon_slot',
        'evening_slot',
        'night_slot',
        'weekends',
        'time_zone',
        'default_open_time',
        'default_close_time',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'default_open_time' => 'datetime:H:i',
        'default_close_time' => 'datetime:H:i',
        'open_time' => 'datetime:H:i',
        'close_time' => 'datetime:H:i',
    ];

    public function getOpenTimeAttribute($value)
    {
        return Carbon::parse($value)->format('h:i A');
    }

    public function getCloseTimeAttribute($value)
    {
        return Carbon::parse($value)->format('h:i A');
    }

    /**
     * Get the hospital that owns the exception.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Check if the hospital is closed for this timing.
     */
    public function getIsClosedAttribute()
    {
        // Check if open_time is set - if not, consider it closed
        return empty($this->open_time);
    }
}
