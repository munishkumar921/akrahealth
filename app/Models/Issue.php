<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'issues';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'patient_id',
        'doctor_id',
        'issue',
        'rcopia_sync',
        'type',
        'reconcile',
        'notes',
        'date_active',
        'date_inactive',
    ];

    /**
     * casts
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'date_active' => 'datetime:M d, Y',
        'date_inactive' => 'datetime:M d, Y',
    ];
}
