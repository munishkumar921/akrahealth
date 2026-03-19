<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlatform extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'description',
        'api_key',
        'secret_key',
        'merchant_id',
        'webhook_url',
        'environment',
        'settings',
        'supported_currencies',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'settings' => 'array',
        'supported_currencies' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * Scope for active platforms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default platform.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for sandbox environment.
     */
    public function scopeSandbox($query)
    {
        return $query->where('environment', 'sandbox');
    }

    /**
     * Scope for live environment.
     */
    public function scopeLive($query)
    {
        return $query->where('environment', 'live');
    }

    /**
     * Get transactions using this platform.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the platform name for display.
     */
    public function getFormattedNameAttribute(): string
    {
        return ucfirst($this->name);
    }

    /**
     * Get environment badge color.
     */
    public function getEnvironmentBadgeAttribute(): string
    {
        return $this->environment === 'sandbox' ? 'warning' : 'success';
    }

    /**
     * Check if the platform is usable.
     */
    public function isUsable(): bool
    {
        return $this->is_active &&
               ! empty($this->api_key) &&
               ! empty($this->secret_key);
    }

    /**
     * Get supported currency list.
     */
    public function getSupportedCurrenciesListAttribute(): string
    {
        if (empty($this->supported_currencies)) {
            return 'N/A';
        }

        return implode(', ', $this->supported_currencies);
    }
}
