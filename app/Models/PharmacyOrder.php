<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyOrder extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'pharmacy_id',
        'patient_id',
        'doctor_id',
        'status',
        'payment_status',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'medications',
        'notes',
        'delivery_address',
        'prescription_file',
        'delivery_required',
        'delivery_status',
        'scheduled_at',
        'dispensed_at',
        'delivered_at',
    ];

    protected $casts = [
        'medications' => 'array',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_required' => 'boolean',
        'scheduled_at' => 'datetime:M d, Y H:i',
        'dispensed_at' => 'datetime:M d, Y H:i',
        'delivered_at' => 'datetime:M d, Y H:i',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    protected $appends = [
        'formatted_status',
        'formatted_payment_status',
        'formatted_amount',
        'medication_count',
    ];

    /**
     * appointment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * pharmacy
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * patient
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * doctor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get formatted status for display
     */
    public function getFormattedStatusAttribute(): string
    {
        $statusMap = [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'processing' => 'Processing',
            'ready' => 'Ready for Pickup',
            'dispensed' => 'Dispensed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
        ];

        return $statusMap[$this->status] ?? ucfirst($this->status ?? 'N/A');
    }

    /**
     * Get formatted payment status for display
     */
    public function getFormattedPaymentStatusAttribute(): string
    {
        $paymentStatusMap = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ];

        return $paymentStatusMap[$this->payment_status] ?? ucfirst($this->payment_status ?? 'N/A');
    }

    /**
     * Get formatted amount for display
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₹'.number_format($this->total_amount ?? 0, 2);
    }

    /**
     * Get medication count from medications array
     */
    public function getMedicationCountAttribute(): int
    {
        if (is_array($this->medications)) {
            return count($this->medications);
        }

        return 0;
    }

    /**
     * Get patient name accessor
     */
    public function getPatientNameAttribute(): string
    {
        if ($this->appointment && $this->appointment->patient) {
            return $this->appointment->patient->name ?? 'N/A';
        }

        return 'N/A';
    }

    /**
     * Get order ID for display
     */
    public function getOrderIdAttribute(): string
    {
        return '#ORD-'.str_pad(substr($this->id, 0, 6), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for pending orders
     *
     * @param \Illuminate\Database\Eloquent.Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed orders
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent.Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled orders
     *
     * @param \Illuminate\Database\Eloquent.Builder $query
     * @return \Illuminate\Database\Eloquent.Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for paid orders
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent.Builder
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for unpaid orders
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for filtering by status
     *
     * @param \Illuminate\Database\Eloquent.Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, string $status)
    {
        if (! empty($status)) {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Scope for filtering by payment status
     *
     * @param \Illuminate\Database\Eloquent.Builder $query
     * @return \Illuminate\Database\Eloquent.Builder
     */
    public function scopeWithPaymentStatus($query, string $paymentStatus)
    {
        if (! empty($paymentStatus)) {
            return $query->where('payment_status', $paymentStatus);
        }

        return $query;
    }

    /**
     * Scope for searching
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, string $keyword)
    {
        if (! empty($keyword)) {
            return $query->whereHas('pharmacy', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%'.$keyword.'%');
            })
                ->orWhereHas('appointment.patient', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%'.$keyword.'%');
                })
                ->orWhereHas('doctor.user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%'.$keyword.'%');
                })
                ->orWhere('id', 'LIKE', '%'.$keyword.'%');
        }

        return $query;
    }
}
