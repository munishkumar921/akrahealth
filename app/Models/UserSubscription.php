<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'start_date',
        'end_date',
        'razorpay_subscription_id',
        'razorpay_order_id',
        'payment_link_id',
        'payment_status',
        'currency',
        'amount',
        'replaced_at',
        'replaced_by',
        'upgraded_from',
    ];

    protected $casts = [
        'start_date' => 'date:M d, Y',
        'end_date' => 'date:M d, Y',
        'replaced_at' => 'datetime:M d, Y',
        'created_at' => 'datetime:M d, Y',
        'updated_at' => 'datetime:M d, Y',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription plan.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function getFormattedPriceAttribute()
    {
        $symbols = [
            'INR' => '₹',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$this->currency] ?? '';

        return $symbol.number_format($this->amount, 2);
    }

    /**
     * Get the number of days left until subscription expiry.
     */
    public function getDaysLeftAttribute()
    {
        if (! $this->end_date) {
            return null;
        }

        $now = now();
        $endDate = $this->end_date;

        if ($endDate->isPast()) {
            return 0;
        }

        return $now->diffInDays($endDate);
    }
}
