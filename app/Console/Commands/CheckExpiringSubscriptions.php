<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionExpiringMail;
use App\Models\UserSubscription;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionExpiringNotification;
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
                Mail::to($subscription->user->email)->send(new SubscriptionExpiringMail($subscription, 3));

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
                Mail::to($subscription->user->email)->send(new SubscriptionExpiringMail($subscription, 1));

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
                Mail::to($subscription->user->email)->send(new SubscriptionExpiredMail($subscription));

                $this->line('Expired subscription notification sent to '.$subscription->user->email);
            }
        }

        $this->info('Subscription check completed successfully.');
    }
}
