<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Immunization extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Table Name
     *
     * @var string
     */
    protected $table = 'immunizations';

    /**
     * Fillable
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'patient_id',
        'doctor_id',
        'encounter_id',
        'current_procedural_terminology_id',
        'date',
        'immunization',
        'sequence',
        'body_site',
        'dosage',
        'dosage_unit',
        'route',
        'elsewhere',
        'vis',
        'lot',
        'manufacturer',
        'expiration',
        'brand',
        'cvx_code',
        'reconcile',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'date' => 'date:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    public function getDateAttribute($value)
    {
        return $value && $value !== '0000-00-00' ? Carbon::parse($value)->format('M d, Y') : null;
    }
}
