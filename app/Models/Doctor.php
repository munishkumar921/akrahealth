<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Doctor extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'doctors';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'hospital_id',
        'name',
        'first_name',
        'last_name',
        'registration_number',
        'certification',
        'government_id_proof',
        'qualification',
        'experience',
        'about',
        'consultation_fee',
        'is_available',
        'dea',
        'tax_id',
        'signature',
        'appointment_slot_duration',
        'is_verified',
        'is_active',
        'in_person_consultation',
        'video_consultation',
        'whatsapp_consultation',
        'recommended_lab_id',
        'recommended_pharmacy_id',
        'doctor_logo',
        'doctor_seal',
        'doctor_signature',
        'selected_patient_id',
        'profile_photo_path',
    ];

    protected $appends = [
        'certificate_doc',
        'id_doc',
        'location',
        'profile_photo_url',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
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
     * myLab
     *
     * @return void
     */
    public function myLab()
    {
        return $this->belongsTo(Lab::class, 'recommended_lab_id');
    }

    /**
     * myPharmacy
     *
     * @return void
     */
    public function myPharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'recommended_pharmacy_id');
    }

    /**
     * user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * appointments
     *
     * @return void
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * prescriptions
     *
     * @return void
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * hospital
     *
     * @return void
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * getCertificateDocAttribute
     *
     * @return void
     */
    public function getCertificateDocAttribute()
    {
        return $this->certification;
    }

    /**
     * getIdDocAttribute
     *
     * @return void
     */
    public function getIdDocAttribute()
    {
        return $this->government_id_proof;
    }

    /**
     * getLocationAttribute
     *
     * @return void
     */
    public function getLocationAttribute()
    {
        return 'https://www.google.com/maps/@12.9342379,80.2503925';
    }

    /**
     * Get the URL for the profile photo.
     *
     * @return string|null
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return asset('storage/'.$this->profile_photo_path);
        }

        return null;
    }

    /**
     * specialities
     */
    public function specialities(): BelongsToMany
    {
        return $this->belongsToMany(Speciality::class, 'doctor_speciality', 'doctor_id', 'speciality_id');
    }

    /**
     * syncKnownLanguages
     *
     * @param  mixed  $languageIds
     */
    public function syncSpecialities(array $speciality_ids): void
    {
        $this->specialities()->sync($speciality_ids);
    }

    /**
     * chat
     *
     * @return void
     */
    public function chat()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * selectedPatient
     *
     * @return void
     */
    public function selectedPatient()
    {
        return $this->belongsTo(patient::class, 'selected_patient_id');
    }

    /**
     * doctorPatients
     */
    public function doctorPatients()
    {
        return $this->hasMany(DoctorPatient::class, 'doctor_id');
    }

    public function providerException()
    {
        return $this->hasMany(ProviderException::class);
    }
}
