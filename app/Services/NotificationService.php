<?php

namespace App\Services;

use App\Mail\LabVerifiedMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(private readonly InAppNotificationService $inAppNotificationService)
    {
    }

    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        $search = $request->search;

        return Notification::query()->when($request->search, function ($q) use ($search) {
            $q->where(function ($query) use ($search) {
                $query->where('type', 'LIKE', "%$search%")
                    ->orWhere('data->title', 'LIKE', "%$search%")
                    ->orWhere('data->message', 'LIKE', "%$search%")
                    ->orWhere('data->channel', 'LIKE', "%$search%");
            });
        })
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))
            ->withQueryString();
    }

    /**
     * send
     *
     * @param  mixed  $data
     * @return void
     */
    public function send(array $data)
    {
        $model = $this->getModelFromUserType($data['user_type']);

        $users = $model::query()
            ->when(
                ! empty($data['user_ids']),
                fn ($q) => $q->whereIn('id', $data['user_ids'])
            )
            ->get()
            ->map(function ($recipient) {
                return $recipient->user ?? $recipient;
            })
            ->filter();

        $payload = $this->inAppNotificationService->buildPayload(
            $data['title'],
            $data['message'],
            $data['type'] ?? 'system',
            [
                'recipient_role' => $data['user_type'] ?? null,
                'channel' => $data['channel'] ?? 'in_app',
            ]
        );

        $this->inAppNotificationService->notifyUsers($users, $payload);

        return ['message' => 'Notification sent successfully'];
    }

    /**
     * getModelFromUserType
     *
     * @param  mixed  $type
     * @return void
     */
    protected function getModelFromUserType($type)
    {
        return match ($type) {
            'patient' => \App\Models\Patient::class,
            'doctor' => \App\Models\Doctor::class,
            'lab' => \App\Models\Lab::class,
            'pharmacy' => \App\Models\Pharmacy::class,
            'delivery_partner' => \App\Models\DeliveryPartner::class,
            default => User::class,
        };
    }

    /**
     * sendLabVerifiedMail
     *
     * @return void
     */
    public function sendLabVerifiedMail(User $user)
    {
        try {
            Mail::to($user->email)->send(new LabVerifiedMail($user));
        } catch (\Throwable $th) {
            // It's good practice to log the exception.
            \Log::error("Failed to send lab verified mail to {$user->email}: ".$th->getMessage());
        }
    }

    /**
     * sendWelcomeMailToUser
     *
     * @return void
     */
    public function sendWelcomeMailToUser(User $user, ?string $password)
    {
        if (empty($password)) {
            return;
        }

        app(EmailNotificationService::class)->queueCredentialsEmail($user, $password);
    }
}
