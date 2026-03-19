<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'name',
        'hospital_id',
        'brand_name',
        'generic_name',
        'composition',
        'dosage_form',
        'strength',
        'route',
        'indications',
        'contraindications',
        'side_effects',
        'precautions',
        'instructions',
        'price',
        'currency',
        'stock_quantity',
        'expiry_date',
        'batch_no',
        'is_prescription_required',
        'is_active',
        'is_encrypted',
    ];

    protected $casts = [
        // 'price' => 'decimal:2',
        'expiry_date' => 'date:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];
}
