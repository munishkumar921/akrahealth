<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
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
        'invoice_number',
        'patient_id',
        'user_id',
        'doctor_id',
        'hospital_id',
        'appointment_id',
        'lab_order_id',
        'pharmacy_order_id',
        'subscription_id',
        'amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'status',
        'razorpay_invoice_id',
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_customer_id',
        'payment_method',
        'due_date',
        'paid_at',
        'sent_at',
        'viewed_at',
        'items',
        'customer_details',
        'notes',
        'terms_conditions',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
        'due_date' => 'date:M d, Y',
        'paid_at' => 'datetime:M d, Y H:i A',
        'sent_at' => 'datetime:M d, Y H:i A',

        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime:M d, Y H:i A',
        'sent_at' => 'datetime:M d, Y H:i A',
        'viewed_at' => 'datetime:M d, Y H:i A',
        'items' => 'array',
        'customer_details' => 'array',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';

    const STATUS_SENT = 'sent';

    const STATUS_VIEWED = 'viewed';

    const STATUS_PARTIAL = 'partial';

    const STATUS_PAID = 'paid';

    const STATUS_OVERDUE = 'overdue';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_FAILED = 'failed';

    /**
     * Default status on creation
     */
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'currency' => 'INR',
    ];

    /**
     * Get the patient that owns the invoice.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the doctor associated with the invoice.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the hospital associated with the invoice.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    /**
     * Get the appointment associated with the invoice.
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Get the lab order associated with the invoice.
     */
    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }

    /**
     * Get the pharmacy order associated with the invoice.
     */
    public function pharmacyOrder()
    {
        return $this->belongsTo(PharmacyOrder::class);
    }

    /**
     * Get the subscription associated with the invoice.
     */
    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class);
    }

    /**
     * Get the transactions for this invoice.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get formatted status for display.
     */
    public function getFormattedStatusAttribute(): string
    {
        $statusMap = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_VIEWED => 'Viewed',
            self::STATUS_PARTIAL => 'Partial Payment',
            self::STATUS_PAID => 'Paid',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FAILED => 'Failed',
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status ?? 'N/A');
    }

    /**
     * Get formatted amount for display.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : ($this->currency ?? '₹');

        return $symbol.number_format($this->total_amount ?? 0, 2);
    }

    /**
     * Get formatted paid amount for display.
     */
    public function getFormattedPaidAmountAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : ($this->currency ?? '₹');
        $paidAmount = $this->transactions()->where('status', 'completed')->sum('amount') ?? 0;

        return $symbol.number_format($paidAmount, 2);
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE ||
            ($this->due_date && $this->due_date->isPast() && $this->status !== self::STATUS_PAID);
    }

    /**
     * Get unpaid amount.
     */
    public function getUnpaidAmountAttribute(): float
    {
        $paidAmount = $this->transactions()->where('status', 'completed')->sum('amount') ?? 0;

        return max(0, ($this->total_amount ?? 0) - $paidAmount);
    }

    /**
     * Get payment progress percentage.
     */
    public function getPaymentProgressAttribute(): int
    {
        if ($this->total_amount <= 0) {
            return 0;
        }
        $paidAmount = $this->transactions()->where('status', 'completed')->sum('amount') ?? 0;

        return min(100, (int) round(($paidAmount / $this->total_amount) * 100));
    }

    /**
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastInvoice = self::whereDate('created_at', today())->orderBy('id', 'desc')->first();
        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $prefix.'-'.$date.'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope for unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_SENT,
            self::STATUS_VIEWED,
            self::STATUS_PARTIAL,
            self::STATUS_OVERDUE,
        ]);
    }

    /**
     * Scope for overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
            ->orWhere(function ($q) {
                $q->whereIn('status', [self::STATUS_SENT, self::STATUS_VIEWED])
                    ->where('due_date', '<', today());
            });
    }

    /**
     * Scope for invoices by status.
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
                $q->where('invoice_number', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('id', 'LIKE', '%'.$keyword.'%')
                    ->orWhereHas('patient', function ($patientQuery) use ($keyword) {
                        $patientQuery->where('name', 'LIKE', '%'.$keyword.'%');
                    })
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('email', 'LIKE', '%'.$keyword.'%');
                    });
            });
        }

        return $query;
    }

    /**
     * Calculate totals from items.
     */
    public function calculateTotals(): self
    {
        $items = $this->items ?? [];
        $subtotal = 0;
        $taxAmount = 0;
        $discountAmount = 0;

        foreach ($items as $item) {
            $subtotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
            $taxAmount += ($item['tax_amount'] ?? 0);
            $discountAmount += ($item['discount_amount'] ?? 0);
        }

        $this->amount = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->discount_amount = $discountAmount;
        $this->total_amount = $subtotal + $taxAmount - $discountAmount;

        return $this;
    }

    /**
     * Mark as sent.
     */
    public function markAsSent(): self
    {
        $this->status = self::STATUS_SENT;
        $this->sent_at = now();
        $this->save();

        return $this;
    }

    /**
     * Mark as viewed.
     */
    public function markAsViewed(): self
    {
        $this->status = self::STATUS_VIEWED;
        $this->viewed_at = now();
        $this->save();

        return $this;
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid(?string $paymentId = null, ?string $paymentMethod = null): self
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
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
     * Mark as overdue.
     */
    public function markAsOverdue(): self
    {
        $this->status = self::STATUS_OVERDUE;
        $this->save();

        return $this;
    }
}
