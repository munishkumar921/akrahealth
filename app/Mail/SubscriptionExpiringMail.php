<?php

namespace App\Mail;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subscription;

    public $daysRemaining;

    /**
     * Create a new message instance.
     */
    public function __construct(UserSubscription $subscription, int $daysRemaining)
    {
        $this->subscription = $subscription;
        $this->daysRemaining = $daysRemaining;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $planName = $this->subscription->subscriptionPlan->title ?? 'Your subscription';
        $daysText = $this->daysRemaining === 1 ? 'tomorrow' : "in {$this->daysRemaining} days";

        return $this->subject("Subscription Expiring {$daysText} - Action Required")
            ->view('emails.subscription-expiring', [
                'subscription' => $this->subscription,
                'planName' => $planName,
                'daysRemaining' => $this->daysRemaining,
                'daysText' => $daysText,
            ]);
    }
}
