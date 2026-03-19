<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * table
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * fillable
     *
     * @var array
     */
    protected $fillable = [
        'address_1',
        'address_2',
        'city',
        'state',
        'zip',
        'country',
        'email',
        'phone',
        'comment',
        'is_default',
        'user_id',
        'hospital_id',
    ];
}
