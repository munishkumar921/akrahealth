<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

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
        'is_encrypted' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getValueAttribute($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $exception) {
            return $value;
        }
    }

    public function setValueAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['value'] = $value;

            return;
        }

        $shouldEncrypt = (bool) ($this->attributes['is_encrypted'] ?? $this->is_encrypted ?? false);

        $this->attributes['value'] = $shouldEncrypt
            ? Crypt::encryptString((string) $value)
            : $value;
    }
}
