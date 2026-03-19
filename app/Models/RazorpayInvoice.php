<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RazorpayInvoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'razorpay_invoice_id',
        'razorpay_customer_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'invoice_number',
        'invoice_id',
        'patient_id',
        'user_id',
        'subscription_id',
        'status',
        'type',
        'currency',
        'amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'amount_due',
        'customer_details',
        'line_items',
        'payment_method',
        'payment_url',
        'short_url',
        'pdf_url',
        'invoice_date',
        'due_date',
        'paid_at',
        'sent_at',
        'expired_at',
        'last_webhook_received_at',
        'last_webhook_payload',
        'notes',
        'terms_conditions',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'customer_details' => 'array',
        'line_items' => 'array',
        'last_webhook_payload' => 'array',
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
        'expired_at' => 'datetime',
        'last_webhook_received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';

    const STATUS_ISSUED = 'issued';

    const STATUS_PAID = 'paid';

    const STATUS_PARTIALLY_PAID = 'partially_paid';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_EXPIRED = 'expired';

    /**
     * Type constants
     */
    const TYPE_INVOICE = 'invoice';

    const TYPE_RECEIPT = 'receipt';

    /**
     * Default attributes
     */
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'type' => self::TYPE_INVOICE,
        'currency' => 'INR',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate UUID on creating
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the invoice that owns this Razorpay invoice.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Get the patient that owns this Razorpay invoice.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user that owns this Razorpay invoice.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that owns this Razorpay invoice.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class);
    }

    /**
     * Scope for paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope for issued invoices.
     */
    public function scopeIssued($query)
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    /**
     * Scope for unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_ISSUED,
            self::STATUS_PARTIALLY_PAID,
        ]);
    }

    /**
     * Scope for expired invoices.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope for cancelled invoices.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for Razorpay invoices by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for invoices by patient.
     */
    public function scopeForPatient($query, string $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope for invoices by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for searching.
     */
    public function scopeSearch($query, string $keyword)
    {
        if (! empty($keyword)) {
            return $query->where(function ($q) use ($keyword) {
                $q->where('razorpay_invoice_id', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('invoice_number', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('id', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('razorpay_payment_id', 'LIKE', '%'.$keyword.'%');
            });
        }

        return $query;
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is issued.
     */
    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    /**
     * Check if invoice is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if invoice is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Get formatted status for display.
     */
    public function getFormattedStatusAttribute(): string
    {
        $statusMap = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIALLY_PAID => 'Partially Paid',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_EXPIRED => 'Expired',
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status ?? 'N/A');
    }

    /**
     * Get formatted amount for display.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₹';

        return $symbol.number_format($this->total_amount ?? 0, 2);
    }

    /**
     * Get formatted paid amount for display.
     */
    public function getFormattedAmountPaidAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '₹';

        return $symbol.number_format($this->amount_paid ?? 0, 2);
    }

    /**
     * Get unpaid amount.
     */
    public function getUnpaidAmountAttribute(): float
    {
        return max(0, ($this->total_amount ?? 0) - ($this->amount_paid ?? 0));
    }

    /**
     * Get payment progress percentage.
     */
    public function getPaymentProgressAttribute(): int
    {
        if ($this->total_amount <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->amount_paid / $this->total_amount) * 100));
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid(?string $paymentId = null, ?string $paymentMethod = null): self
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
        $this->amount_paid = $this->total_amount;
        $this->amount_due = 0;

        if ($paymentId) {
            $this->razorpay_payment_id = $paymentId;
        }
        if ($paymentMethod) {
            $this->payment_method = $paymentMethod;
        }

        $this->save();

        return $this;
    }

    /**
     * Mark as issued.
     */
    public function markAsIssued(): self
    {
        $this->status = self::STATUS_ISSUED;
        $this->sent_at = now();
        $this->invoice_date = now();
        $this->save();

        return $this;
    }

    /**
     * Mark as partially paid.
     */
    public function markAsPartiallyPaid(float $amountPaid, ?string $paymentId = null): self
    {
        $this->status = self::STATUS_PARTIALLY_PAID;
        $this->amount_paid = $amountPaid;
        $this->amount_due = max(0, $this->total_amount - $amountPaid);

        if ($paymentId) {
            $this->razorpay_payment_id = $paymentId;
        }

        $this->save();

        return $this;
    }

    /**
     * Mark as expired.
     */
    public function markAsExpired(): self
    {
        $this->status = self::STATUS_EXPIRED;
        $this->expired_at = now();
        $this->save();

        return $this;
    }

    /**
     * Mark as cancelled.
     */
    public function markAsCancelled(): self
    {
        $this->status = self::STATUS_CANCELLED;
        $this->save();

        return $this;
    }

    /**
     * Update webhook data.
     */
    public function updateWebhookData(array $payload): self
    {
        $this->last_webhook_received_at = now();
        $this->last_webhook_payload = $payload;
        $this->save();

        return $this;
    }

    /**
     * Get customer email from customer details.
     */
    public function getCustomerEmailAttribute(): ?string
    {
        return $this->customer_details['email'] ?? null;
    }

    /**
     * Get customer name from customer details.
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->customer_details['name'] ?? 'Customer';
    }

    /**
     * Get customer phone from customer details.
     */
    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->customer_details['phone'] ?? null;
    }
}
