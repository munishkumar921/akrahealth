<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VaccineInventory extends Model
{
    use SoftDeletes;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'vaccines';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'date_purchase',
        'immunization',
        'lot',
        'manufacturer',
        'expiration',
        'brand',
        'code',
        'cpt',
        'quantity',
        'hospital_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_purchase' => 'date:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'expiration' => 'date:M d, Y',
        'quantity' => 'integer',
    ];

    /**
     * Get the hospital associated with the vaccine inventory.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
