<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'hospital_id',
        'document_id',
        'from',
        'to',
        'subject',
        'cc',
        'message',
        'read_at',
        'date',
        'read',
        't_messages_id',
        'mailbox',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'read_at' => 'datetime:M d, Y',
        'date' => 'datetime:M d, Y',
        'read' => 'boolean',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
