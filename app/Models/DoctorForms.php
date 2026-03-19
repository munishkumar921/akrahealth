<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DoctorForms extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'form',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
