<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BillingCore extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'billing_cores';

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'hospital_id',
        'other_billing_id',
        'cpt',
        'cpt_charge',
        'icd_pointer',
        'unit',
        'modifier',
        'dos_f',
        'dos_t',
        'billing_group',
        'payment',
        'reason',
        'payment_type',
        'service_start',
        'service_end',
    ];

    protected $casts = [
        // 'cpt_charge' => 'decimal:2', // Disabled to preserve full precision for billing calculations
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'service_start' => 'datetime:Y-m-d H:i:s',
        'service_end' => 'datetime:Y-m-d H:i:s',
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
