<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'images';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'encounter_id',
        'doctor_id',
        'patient_id',
        'name',
        'url',
        'description',
        'type',
    ];
}
