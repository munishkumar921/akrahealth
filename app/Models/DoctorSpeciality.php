<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DoctorSpeciality extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'doctor_speciality';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id',
        'speciality_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }
}
