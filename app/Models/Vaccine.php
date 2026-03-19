<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

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

    public function getExpirationAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    public function getQuantityAttribute($value)
    {
        return intval($value);
    }

    /**
     * Get the immunization associated with the vaccine inventory.
     */
    public function getDatePurchaseAttribute($value)
    {
        return Carbon::parse($value)->format('M d, Y');
    }

    /**
     * Get the hospital associated with the vaccine inventory.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
