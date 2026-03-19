<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'billings';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'hospital_id',
        'insurance_id_1',
        'insurance_id_2',
    ];

    public function insurance()
    {
        return $this->belongsTo(Insurance::class, 'insurance_id_1');
    }
}
