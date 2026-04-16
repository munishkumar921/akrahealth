<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionExpiringMail;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionExpiringNotification;
use App\Services\EmailNotificationService;
use App\Services\InAppNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckExpiringSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring subscriptions and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $threeDaysFromNow = $now->copy()->addDays(3)->toDateString();
        $oneDayFromNow = $now->copy()->addDay()->toDateString();

        // Get active subscriptions expiring in 3 days
        $expiringIn3Days = UserSubscription::with(['user', 'subscriptionPlan'])
            ->where('status', 'active')
            ->whereDate('end_date', $threeDaysFromNow)
            ->get();

        $this->info('Found '.$expiringIn3Days->count().' subscriptions expiring in 3 days.');

        foreach ($expiringIn3Days as $subscription) {
            if ($subscription->user) {
                // Send notification
                $subscription->user->notify(new SubscriptionExpiringNotification($subscription, 3));

                // Send email
                Mail::to($subscription->user->email)->queue(new SubscriptionExpiringMail($subscription, 3));
                app(InAppNotificationService::class)->notifySuperAdmins(
                    app(InAppNotificationService::class)->buildPayload(
                        'Trial ending soon',
                        ($subscription->user->name ?? 'A user')."'s subscription ends in 3 days.",
                        'trial_ending_soon',
                        [
                            'related_model_type' => UserSubscription::class,
                            'related_model_id' => $subscription->id,
                            'meta' => [
                                'days_remaining' => 3,
                                'plan' => $subscription->subscriptionPlan?->title,
                            ],
                        ]
                    )
                );

                $this->line('Notification sent to '.$subscription->user->email.' - Subscription expiring in 3 days');
            }
        }

        // Get active subscriptions expiring in 1 day
        $expiringIn1Day = UserSubscription::with(['user', 'subscriptionPlan'])
            ->where('status', 'active')
            ->whereDate('end_date', $oneDayFromNow)
            ->get();

        $this->info('Found '.$expiringIn1Day->count().' subscriptions expiring in 1 day.');

        foreach ($expiringIn1Day as $subscription) {
            if ($subscription->user) {
                // Send notification
                $subscription->user->notify(new SubscriptionExpiringNotification($subscription, 1));

                // Send email
                Mail::to($subscription->user->email)->queue(new SubscriptionExpiringMail($subscription, 1));
                app(InAppNotificationService::class)->notifySuperAdmins(
                    app(InAppNotificationService::class)->buildPayload(
                        'Trial ending soon',
                        ($subscription->user->name ?? 'A user')."'s subscription ends in 1 day.",
                        'trial_ending_soon',
                        [
                            'related_model_type' => UserSubscription::class,
                            'related_model_id' => $subscription->id,
                            'meta' => [
                                'days_remaining' => 1,
                                'plan' => $subscription->subscriptionPlan?->title,
                            ],
                        ]
                    )
                );

                $this->line('Notification sent to '.$subscription->user->email.' - Subscription expiring in 1 day');
            }
        }

        // Get subscriptions that expired by date (status should flip immediately after expiry date).
        $expiredDate = $now->toDateString();

        $expiredToday = UserSubscription::with(['user', 'subscriptionPlan'])
            ->where('status', 'active')
            ->whereDate('end_date', '<', $expiredDate)
            ->get();

        $this->info('Found '.$expiredToday->count().' expired subscriptions.');

        foreach ($expiredToday as $subscription) {
            // Update subscription status to expired
            $subscription->update(['status' => 'expired']);

            if ($subscription->user) {
                // Send notification
                $subscription->user->notify(new SubscriptionExpiredNotification($subscription));

                // Send email
                Mail::to($subscription->user->email)->queue(new SubscriptionExpiredMail($subscription));
                app(EmailNotificationService::class)->queueSubscriptionExpiredAdminAlert($subscription);
                app(InAppNotificationService::class)->notifySuperAdmins(
                    app(InAppNotificationService::class)->buildPayload(
                        'Subscription expired',
                        ($subscription->user->name ?? 'A user')."'s subscription expired today.",
                        'subscription_expired',
                        [
                            'related_model_type' => UserSubscription::class,
                            'related_model_id' => $subscription->id,
                            'meta' => [
                                'plan' => $subscription->subscriptionPlan?->title,
                                'expired_on' => $subscription->end_date,
                            ],
                        ]
                    )
                );

                $this->line('Expired subscription notification sent to '.$subscription->user->email);
            }
        }

        $this->info('Subscription check completed successfully.');
    }
}
