<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineTemperature extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'vaccine_temperatures';

    /**
     * primaryKey
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'temperature',
        'date',
        'time',
        'action',
        'hospital_id',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'date' => 'date:M d, Y',
        'time' => 'datetime:h:i A',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }
}
