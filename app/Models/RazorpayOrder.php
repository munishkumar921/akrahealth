<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Razorpay Order Model
 * Stores Razorpay order information for payments
 */
class RazorpayOrder extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'razorpay_payment_id',
        'invoice_id',
        'user_id',
        'patient_id',
        'subscription_id',
        'amount',
        'currency',
        'status',
        'receipt',
        'attempts',
        'notes',
        'paid_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'attempts' => 'integer',
        'notes' => 'array',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_CREATED = 'created';

    const STATUS_ATTEMPTED = 'attempted';

    const STATUS_PAID = 'paid';

    /**
     * Default attributes
     */
    protected $attributes = [
        'status' => self::STATUS_CREATED,
        'currency' => 'INR',
        'attempts' => 0,
    ];

    /**
     * Get the user that owns this order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patient associated with this order.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the invoice associated with this order.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the subscription associated with this order.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Get formatted amount for display.
     */
    public function getFormattedAmountAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₹';

        return $symbol.number_format($this->amount ?? 0, 2);
    }

    /**
     * Scope for paid orders.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope for unpaid orders.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_CREATED, self::STATUS_ATTEMPTED]);
    }

    /**
     * Scope for orders by user.
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for orders by subscription.
     */
    public function scopeForSubscription($query, string $subscriptionId)
    {
        return $query->where('subscription_id', $subscriptionId);
    }
}
