<?php

namespace App\Mail;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subscription;

    /**
     * Create a new message instance.
     */
    public function __construct(UserSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $planName = $this->subscription->subscriptionPlan->title ?? 'Your subscription';

        return $this->subject('Subscription Expired - Renewal Required')
            ->view('emails.subscription-expired', [
                'subscription' => $this->subscription,
                'planName' => $planName,
            ]);
    }
}
