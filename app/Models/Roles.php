<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
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
        'guard_name',
        'is_active',
        'created_at',
        'updated_at',
    ];
}
