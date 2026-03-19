<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'schedules';

    protected $fillable = [
        'slug',
        'doctor_id',
        'patient_id',
        'duration',
        'start_date',
        'start_time',
        'visit_type',
        'reason',
        'notes',
    ];
}
