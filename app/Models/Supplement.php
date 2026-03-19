<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplement extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'supplements';

    protected $fillable = [
        'hospital_id',
        'purchase_date',
        'description',
        'strength',
        'manufacturer',
        'expiration',
        'cpt',
        'charge',
        'quantity',
        'sup_lot',
    ];

    protected $casts = [
        'purchase_date' => 'date:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'charge' => 'decimal:2',
        'expiration' => 'date:M d, Y',
        'quantity' => 'integer',
    ];
}
