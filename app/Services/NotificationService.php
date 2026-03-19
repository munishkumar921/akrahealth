<?php

namespace App\Services;

use App\Mail\LabVerifiedMail;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * list
     *
     * @param  mixed  $request
     * @return void
     */
    public function list($request)
    {
        $search = $request->search;

        return Notification::query()
            ->when($request->search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('message', 'LIKE', "%$search%")
                        ->orWhere('title', 'LIKE', "%$search%")
                        ->orWhere('type', 'LIKE', "%$search%")
                        ->orWhere('channel', 'LIKE', "%$search%")
                        ->orWhere('status', 'LIKE', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(request('per_page', paginateLimit()))->withQueryString();
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
            ->get();

        foreach ($users as $user) {
            $user->notify(new GenericNotification($data['title'], $data['message']));
        }

        foreach ($users as $user) {
            Notification::create([
                'title' => $data['title'],
                'message' => $data['message'],
                'user_type' => $data['user_type'],
                'user_id' => $user->id,
            ]);
        }

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

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ];
        try {
            Mail::to($user->email)->queue(new \App\Mail\UserCredentialsMail($data));
        } catch (\Throwable $th) {
            \Log::error("Failed to queue welcome mail to {$user->email}: ".$th->getMessage());
        }
    }
}
