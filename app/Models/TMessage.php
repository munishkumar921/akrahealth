<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TMessage extends Model
{
    use HasFactory;

    /**
     * table
     *
     * @var string
     */
    protected $table = 't_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'hospital_id',
        'to',
        'from',
        'messages_signed',
        'date',
        'messages_dos',
        'subject',
        'message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'messages_dos' => 'date',
    ];

    /**
     * Get the patient associated with the message.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor associated with the message.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the hospital associated with the message.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
