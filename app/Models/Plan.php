<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'plans';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'encounter_id',
        'date',
        'plan',
        'duration',
        'followup',
        'goals',
        'tp',
    ];

    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'date' => 'date:M d, Y',
    ];
}
