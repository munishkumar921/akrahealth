<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasUuids;

    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $fillable = [
        'uuid',
        'name',
        'description',
        'category',
        'banner',
        'is_active',
    ];
}
