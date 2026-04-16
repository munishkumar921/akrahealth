<?php

namespace App\Listeners;

use App\Services\InAppNotificationService;
use Illuminate\Queue\Events\JobFailed;

class NotifySuperAdminsOfFailedJob
{
    public function __construct(
        protected InAppNotificationService $notificationService
    ) {}

    public function handle(JobFailed $event): void
    {
        $jobName = $event->job->resolveName();
        $payload = $event->job->payload();
        $displayName = (string) ($payload['displayName'] ?? $jobName ?? 'Unknown job');
        $type = 'background_job_failure';
        $title = 'Background job failure';
        $message = "A queued job failed: {$displayName}.";

        if (str_contains(strtolower($displayName), 'notification')) {
            $type = 'notification_dispatch_failed';
            $title = 'Notification dispatch failed';
            $message = "A notification dispatch job failed: {$displayName}.";
        } elseif (str_contains(strtolower($displayName), 'subscription')) {
            $type = 'subscription_renewal_sync_failed';
            $title = 'Subscription renewal sync failed';
            $message = "A subscription-related background job failed: {$displayName}.";
        } elseif (str_contains(strtolower($displayName), 'invoice')) {
            $type = 'invoice_sync_failed';
            $title = 'Invoice sync failed';
            $message = "An invoice-related background job failed: {$displayName}.";
        } elseif (str_contains(strtolower($displayName), 'order')) {
            $type = 'order_dispatch_failed';
            $title = 'Order processing failed';
            $message = "An order-related background job failed: {$displayName}.";
        }

        $this->notificationService->notifySuperAdmins(
            $this->notificationService->buildPayload(
                $title,
                $message,
                $type,
                [
                    'meta' => [
                        'job' => $displayName,
                        'connection' => $event->connectionName,
                        'queue' => $event->job->getQueue(),
                        'error' => $event->exception->getMessage(),
                    ],
                ]
            )
        );
    }
}
