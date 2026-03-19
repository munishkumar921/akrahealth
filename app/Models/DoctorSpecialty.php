<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSpecialty extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    public function specialties(): BelongsTo
    {
        return $this->BelongsTo(Speciality::class, 'specialty_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->BelongsTo(Doctor::class, 'id');
    }
}
