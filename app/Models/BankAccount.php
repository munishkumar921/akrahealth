<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_holder_name',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch_address',
        'account_type',
        'is_primary',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * Get the user that owns the bank account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active bank accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include primary bank accounts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to order by primary first, then by creation date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Get masked account number for display purposes.
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        if (strlen($this->account_number) <= 4) {
            return $this->account_number;
        }

        return '****'.substr($this->account_number, -4);
    }

    /**
     * Check if this is the primary account.
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Set this account as primary and unset other primary accounts.
     */
    public function setAsPrimary(): void
    {
        // Unset primary flag from all other accounts for this user
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this account as primary
        $this->update(['is_primary' => true]);
    }
}
