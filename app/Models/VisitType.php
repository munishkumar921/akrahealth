<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VisitType extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    protected $table = 'visit_types';

    public $incrementing = false;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'colors',
        'is_active',
        'hospital_id',
        'doctor_id',
        'currency',
        'price',
        'duration',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
