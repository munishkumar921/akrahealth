<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Razorpay Transaction Model
 * Stores Razorpay payment transaction information
 */
class RazorpayTransaction extends Model
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
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_refund_id',
        'user_id',
        'patient_id',
        'invoice_id',
        'subscription_id',
        'amount',
        'currency',
        'status',
        'method',
        'card_type',
        'card_last4',
        'bank',
        'wallet',
        'vpa',
        'fee',
        'tax',
        'notes',
        'captured_at',
        'refunded_at',
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
        'fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'notes' => 'array',
        'captured_at' => 'datetime',
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_CREATED = 'created';

    const STATUS_PENDING = 'pending';

    const STATUS_CAPTURED = 'captured';

    const STATUS_REFUNDED = 'refunded';

    const STATUS_FAILED = 'failed';

    /**
     * Default attributes
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'currency' => 'INR',
    ];

    /**
     * Get the user that owns this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the patient associated with this transaction.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the invoice associated with this transaction.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the subscription associated with this transaction.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class);
    }

    /**
     * Check if transaction is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_CAPTURED;
    }

    /**
     * Check if transaction is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
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
     * Get payment method display name.
     */
    public function getMethodDisplayAttribute(): string
    {
        $methods = [
            'card' => 'Credit/Debit Card',
            'netbanking' => 'Net Banking',
            'wallet' => 'Wallet',
            'upi' => 'UPI',
            'emi' => 'EMI',
            'cash' => 'Cash',
        ];

        return $methods[$this->method] ?? ucfirst($this->method ?? 'Unknown');
    }

    /**
     * Scope for successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_CAPTURED);
    }

    /**
     * Scope for refunded transactions.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    /**
     * Scope for transactions by user.
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for transactions by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
