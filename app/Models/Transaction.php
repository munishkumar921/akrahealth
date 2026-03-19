<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'lab_order_id',
        'pharmacy_order_id',
        'patient_id',
        'user_id',
        'invoice_id',
        'payment_type',
        'amount',
        'currency',
        'status',
        'transaction_id',
        'razorpay_invoice_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the lab order associated with the transaction.
     */
    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    /**
     * Get the lab through lab order.
     */
    public function lab()
    {
        return $this->hasOneThrough(
            Lab::class,
            LabOrder::class,
            'id',
            'id',
            'lab_order_id',
            'lab_id'
        );
    }

    /**
     * Get the pharmacy order associated with the transaction.
     */
    public function pharmacyOrder()
    {
        return $this->belongsTo(PharmacyOrder::class, 'pharmacy_order_id');
    }

    /**
     * Get the patient that owns the transaction.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice associated with the transaction.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get formatted status for display.
     */
    public function getFormattedStatusAttribute(): string
    {
        $statusMap = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status ?? 'N/A');
    }

    /**
     * Get formatted amount for display.
     */
    public function getFormattedAmountAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : ($this->currency ?? '$');

        return $symbol.number_format($this->amount ?? 0, 2);
    }

    /**
     * Scope for completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for lab order transactions.
     */
    public function scopeForLabOrders($query)
    {
        return $query->whereNotNull('lab_order_id');
    }

    /**
     * Scope for pharmacy order transactions.
     */
    public function scopeForPharmacyOrders($query)
    {
        return $query->whereNotNull('pharmacy_order_id');
    }

    /**
     * Scope for filtering by payment type.
     */
    public function scopeWithPaymentType($query, string $paymentType)
    {
        if (! empty($paymentType)) {
            return $query->where('payment_type', $paymentType);
        }

        return $query;
    }

    /**
     * Scope for invoice transactions.
     */
    public function scopeForInvoice($query, string $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    /**
     * Scope for Razorpay transactions.
     */
    public function scopeForRazorpay($query)
    {
        return $query->where('payment_type', 'razorpay');
    }

    /**
     * Scope for transactions with invoice.
     */
    public function scopeWithInvoice($query)
    {
        return $query->whereNotNull('invoice_id');
    }

    /**
     * Scope for searching.
     */
    public function scopeSearch($query, string $keyword)
    {
        if (! empty($keyword)) {
            return $query->whereHas('patient', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
            })
                ->orWhere('id', 'LIKE', '%'.$keyword.'%')
                ->orWhere('transaction_id', 'LIKE', '%'.$keyword.'%')
                ->orWhere('payment_type', 'LIKE', '%'.$keyword.'%');
        }

        return $query;
    }
}
