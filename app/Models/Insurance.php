<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'insurances';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'plan_name',
        'insurance_company',
        'address_id',
        'patient_id',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
