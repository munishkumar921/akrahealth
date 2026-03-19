<?php

namespace App\Notifications;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public UserSubscription $subscription)
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

        return [
            'subscription_id' => $this->subscription->id,
            'subscription_plan' => $planName,
            'end_date' => $this->subscription->end_date->format('Y-m-d'),
            'message' => "Your {$planName} subscription has expired. Please renew to continue using all features.",
            'action_url' => route('subscriptions.renew', $this->subscription->id),
            'type' => 'subscription_expired',
        ];
    }

    public function toMail(object $notifiable)
    {
        $planName = $this->subscription->subscriptionPlan->title ?? 'Your subscription';

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Subscription Expired - Renewal Required')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("Your {$planName} subscription has expired.")
            ->line('**Subscription Details:**')
            ->line('**Plan:** '.$planName)
            ->line('**Expired Date:** '.$this->subscription->end_date->format('l, F j, Y'))
            ->line('**Amount:** '.($this->subscription->subscriptionPlan->currency ?? 'INR').' '.($this->subscription->subscriptionPlan->price ?? 0))
            ->line('⚠️ **Important:** Your access to premium features has been restricted. Please renew your subscription to restore full access.')
            ->action('Renew Subscription Now', route('subscriptions.renew', $this->subscription->id))
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Best regards, AKRA Health Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'message' => 'Your subscription has expired.',
        ];
    }
}
