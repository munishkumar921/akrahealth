<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'address_1',
        'address_2',
        'city',
        'state',
        'zip',
        'country',
        'sex',
        'is_active',
        'marital_status',
        'photo',
        'blood_group',
        'height_cm',
        'weight_kg',
        'dob',
        'registration_code',
        'existing_conditions',
        'current_medications',
        'allergies',
        'preferred_doctor_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'insurance_provider',
        'insurance_policy_number',
        'insurance_group_number',
        'insurance_phone',
        'insurance_address',
        'guardian_id',
        'guardian_name',
        'guardian_phone',
        'guardian_relationship',
        'guardian_address',

    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'dob' => 'date:M d, Y',
        'height_cm' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'is_active' => 'boolean',

    ];

    public function getNameAttribute()
    {
        return $this->name
            ?? trim(
                ($this->first_name ?? $this->user?->first_name ?? '').
                    ' '.
                    ($this->last_name ?? $this->user?->last_name ?? '')
            );
    }

    /**
     * table
     *
     * @var string
     */
    protected $table = 'patients';

    public function scopeForHospital($query, $hospitalId)
    {
        return $query->where('hospital_id', $hospitalId);
    }

    /**
     * user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * guardian
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }

    /**
     * preferredDoctor
     */
    public function preferredDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'preferred_doctor_id');
    }

    /**
     * issues
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(Issue::class, 'patient_id');
    }

    /**
     * address
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'user_id', 'user_id');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'patient_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'patient_id');
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class, 'patient_id');
    }

    /**
     * completedEncounters
     */
    public function completedEncounters(): HasMany
    {
        return $this->encounters()->whereNotNull('date_signed');
    }

    /**
     * pendingEncounters
     */
    public function pendingEncounters(): HasMany
    {
        return $this->encounters()->whereNull('date_signed');
    }

    public function supplements(): HasMany
    {
        return $this->hasMany(PatientSupplement::class, 'patient_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'patient_id');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class, 'patient_id');
    }

    public function immunizations(): HasMany
    {
        return $this->hasMany(Immunization::class, 'patient_id');
    }

    /**
     * doctorPatients
     */
    public function doctorPatients(): HasMany
    {
        return $this->hasMany(DoctorPatient::class, 'patient_id');
    }

    /**
     * socialHistory
     */
    public function socialHistory(): HasOne
    {
        return $this->hasOne(SocialHistory::class, 'patient_id');
    }
}
