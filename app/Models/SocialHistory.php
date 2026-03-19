<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialHistory extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'social_history',
        'sexually_active',
        'diet',
        'physical_activity',
        'employment',
        'tobacco_use',
        'alcohol_use',
        'drug_use',
        'mental_health_notes',
        'tobacco_use_details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sexually_active' => 'boolean',
        'tobacco_use' => 'boolean',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * Get the patient that owns the social history.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor that owns the social history.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
