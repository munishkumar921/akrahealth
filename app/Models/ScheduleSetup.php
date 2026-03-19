<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleSetup extends Model
{
    protected $fillable = [
        'user_id',
        'hospital_id',
        'visit_type',
        'duration',
        'open_time',
        'close_time',
        'color',
        'provider_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the schedule setup.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the hospital associated with the schedule setup.
     */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the provider (doctor) associated with the schedule setup.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'provider_id');
    }
}
