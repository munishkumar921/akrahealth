<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public UserSubscription $subscription, public int $daysRemaining)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $planName = $this->subscription->subscriptionPlan->title ?? 'Your subscription';
        $message = $this->daysRemaining === 1
            ? "Your {$planName} subscription expires tomorrow. Please renew to continue using all features."
            : "Your {$planName} subscription expires in {$this->daysRemaining} days. Please renew to continue using all features.";

        return [
            'subscription_id' => $this->subscription->id,
            'subscription_plan' => $planName,
            'end_date' => $this->subscription->end_date->format('Y-m-d'),
            'days_remaining' => $this->daysRemaining,
            'message' => $message,
            'action_url' => route('subscriptions.renew', $this->subscription->id),
            'type' => 'subscription_expiring',
        ];
    }

    public function toMail(object $notifiable)
    {
        $planName = $this->subscription->subscriptionPlan->title ?? 'Your subscription';
        $daysText = $this->daysRemaining === 1 ? 'tomorrow' : "in {$this->daysRemaining} days";

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("Subscription Expiring {$daysText} - Action Required")
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your {$planName} subscription is expiring {$daysText}.")
            ->line('**Subscription Details:**')
            ->line('**Plan:** '.$planName)
            ->line('**Expiry Date:** '.$this->subscription->end_date->format('l, F j, Y'))
            ->line('**Amount:** '.($this->subscription->subscriptionPlan->currency ?? 'INR').' '.($this->subscription->subscriptionPlan->price ?? 0))
            ->line('To continue using all features without interruption, please renew your subscription now.')
            ->action('Renew Subscription', route('subscriptions.renew', $this->subscription->id))
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Best regards, AKRA Health Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'days_remaining' => $this->daysRemaining,
            'message' => "Your subscription expires in {$this->daysRemaining} days.",
        ];
    }
}
