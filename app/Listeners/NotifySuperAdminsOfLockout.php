<?php

namespace App\Listeners;

use App\Services\InAppNotificationService;
use Illuminate\Auth\Events\Lockout;

class NotifySuperAdminsOfLockout
{
    public function __construct(
        protected InAppNotificationService $notificationService
    ) {}

    public function handle(Lockout $event): void
    {
        $email = (string) $event->request->input('email', 'unknown');
        $ip = $event->request->ip();

        $this->notificationService->notifySuperAdmins(
            $this->notificationService->buildPayload(
                'Repeated failed login attempts',
                'A user was temporarily locked out after repeated failed login attempts.',
                'security_lockout',
                [
                    'meta' => [
                        'email' => $email,
                        'ip' => $ip,
                        'path' => $event->request->path(),
                    ],
                ]
            )
        );
    }
}
