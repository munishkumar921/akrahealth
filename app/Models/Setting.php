<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected array $encryptAble = ['value'];

    protected $formatted_dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'uuid',
        'key',
        'value',
        'type',
        'description',
        'group',
        'is_encrypted',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'encrypted',
    ];
}
