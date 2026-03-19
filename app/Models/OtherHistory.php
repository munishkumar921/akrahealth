<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherHistory extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'other_histories';

    protected $fillable = [
        'id',
        'patient_id',
        'encounter_id',
        'oh_date',
        'relation',
        'encounter_provider',
        'notes',
        'disease',
        'status',
        'gender',
        'date_of_Birth',
        'marital_status',
        'mother',
        'father',
        'medical_history',
        'past_history',
        'alcohal_history',
        'oh_fh',
        'tobacc_history',
        'drug',
        'medications',
        'supplements',
        'allergies',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $guarded = [];

    protected $dates = [
        'oh_date',
        'date_of_Birth',
    ];

    protected $casts = [
        'oh_date' => 'datetime:M d, Y',
        'date_of_Birth' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class, 'encounter_id');
    }
}
