<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'gender',
        'age',
        'content',
        'destination',
        'content_text',
        'data',
        'date',
        'patient_id',
        'doctor_id',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'forms';

    protected $casts = [
        'date' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * patient
     *
     * @return void
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * doctor
     *
     * @return void
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
