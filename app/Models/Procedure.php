<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'procedures';

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'encounter_provider',
        'date',
        'type',
        'cpt',
        'description',
        'complications',
        'ebl',
    ];
}
