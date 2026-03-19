<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'allergies';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'patient_id',
        'doctor_id',
        'allergies_medicine',
        'allergies_reaction',
        'allergies_severity',
        'reconcile',
        'rcopia_sync',
        'medicine_ndcid',
        'notes',
        'date_active',
        'date_inactive',
    ];

    protected $casts = [
        'date_active' => 'datetime:M d, Y',
        'date_inactive' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function getDateActiveAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
