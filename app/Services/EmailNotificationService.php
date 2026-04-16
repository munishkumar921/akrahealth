<?php

namespace App\Services;

use App\Mail\SystemAlertMail;
use App\Mail\UserCredentialsMail;
use App\Mail\UserVerificationMail;
use App\Mail\WelcomeMail;
use App\Models\Doctor;
use App\Models\Encounter;
use App\Models\Hospital;
use App\Models\Invoice;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    public function queueVerificationEmail(User $user, string $token): void
    {
        $this->queueTo($user->email, new UserVerificationMail(['token' => $token]));
    }

    public function queueWelcomeEmail(User $user): void
    {
        $this->queueTo($user->email, new WelcomeMail(['name' => $user->name]));
    }

    public function queueCredentialsEmail(User $user, string $password): void
    {
        $this->queueTo($user->email, new UserCredentialsMail([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ]));
    }

    public function queueAdminsForHospital(?string $hospitalId, array $payload): void
    {
        if (! $hospitalId) {
            return;
        }

        $emails = $this->resolveAdminUsersForHospital($hospitalId)
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($emails)) {
            return;
        }

        $this->queueTo($emails, new SystemAlertMail($payload));
    }

    public function queueUserAddedAdminAlert(User $newUser, ?string $hospitalId, ?string $roleLabel = null): void
    {
        // $roleLabel = $roleLabel ?: ($newUser->roles->pluck('name')->implode(', ') ?: 'User');
        // $this->queueAdminsForHospital($hospitalId, [
        //     'subject' => 'New user added to your account',
        //     'headline' => 'A new user was added',
        //     'greeting' => 'Hello,',
        //     'message' => "{$newUser->name} has been added to your account.",
        //     'details' => [
        //         'Role: '.$roleLabel,
        //         'Email: '.($newUser->email ?: 'N/A'),
        //         'Mobile: '.($newUser->mobile ?: 'N/A'),
        //     ],
        //     'action_label' => 'Open Users',
        //     'action_url' => route('admin.users.index'),
        // ]);
    }

    public function queueSubscriptionUpgradeAdminAlert(UserSubscription $subscription): void
    {
        $subscription->loadMissing(['user', 'subscriptionPlan']);

        $hospitalId = Hospital::where('user_id', $subscription->user_id)->value('id');

        $this->queueAdminsForHospital($hospitalId, [
            'subject' => 'Subscription upgraded',
            'headline' => 'Subscription upgraded',
            'greeting' => 'Hello,',
            'message' => ($subscription->user?->name ?? 'A user').' upgraded the subscription plan.',
            'details' => [
                'Plan: '.($subscription->subscriptionPlan?->title ?? 'N/A'),
                'Status: '.($subscription->status ?? 'N/A'),
                'Valid until: '.($subscription->end_date ?? 'N/A'),
            ],
            'action_label' => 'View Subscription',
            'action_url' => route('admin.subscription'),
        ]);
    }

    public function queueSubscriptionExpiredAdminAlert(UserSubscription $subscription): void
    {
        $subscription->loadMissing(['user', 'subscriptionPlan']);

        $hospitalId = Hospital::where('user_id', $subscription->user_id)->value('id');

        $this->queueAdminsForHospital($hospitalId, [
            'subject' => 'Subscription expired',
            'headline' => 'Subscription expired',
            'greeting' => 'Hello,',
            'message' => ($subscription->user?->name ?? 'A user')."'s subscription has expired.",
            'details' => [
                'Plan: '.($subscription->subscriptionPlan?->title ?? 'N/A'),
                'Expired on: '.($subscription->end_date ?? 'N/A'),
            ],
            'action_label' => 'Review Subscription',
            'action_url' => route('admin.subscription'),
        ]);
    }

    public function queueDoctorEncounterEmail(?Doctor $doctor, Encounter $encounter): void
    {
        if (! $doctor?->user?->email) {
            return;
        }

        $patientName = $encounter->patient?->name ?? 'Patient';

        $this->queueTo($doctor->user->email, new SystemAlertMail([
            'subject' => 'New encounter created',
            'headline' => 'Encounter created',
            'greeting' => 'Hello Dr. '.$doctor->user->name.',',
            'message' => "A new encounter has been created for {$patientName}.",
            'details' => [
                'Encounter ID: '.$encounter->id,
                'Date of service: '.($encounter->encounter_date_of_service ?? 'N/A'),
                'Patient: '.$patientName,
            ],
            'action_label' => 'Open Encounter',
            'action_url' => route('doctor.encounters.show', $encounter->id),
        ]));
    }

    public function queueDoctorBillingEmail(?User $doctorUser, Invoice $invoice, string $message = 'A billing update is available for your patient.'): void
    {
        if (! $doctorUser?->email) {
            return;
        }

        $this->queueTo($doctorUser->email, new SystemAlertMail([
            'subject' => 'Billing update',
            'headline' => 'Billing update',
            'greeting' => 'Hello Dr. '.$doctorUser->name.',',
            'message' => $message,
            'details' => [
                'Invoice Number: '.($invoice->invoice_number ?? 'N/A'),
                'Status: '.($invoice->status ?? 'N/A'),
                'Amount: '.(($invoice->currency ?? 'INR').' '.number_format((float) ($invoice->total_amount ?? 0), 2)),
            ],
            'action_label' => 'Open Billing',
            'action_url' => route('doctor.billing.index'),
        ]));
    }

    public function queueDoctorOrderWorkflowEmail(?User $doctorUser, string $subject, string $message, array $details = [], ?string $actionUrl = null, ?array $attachment = null): void
    {
        if (! $doctorUser?->email) {
            return;
        }

        $payload = [
            'subject' => $subject,
            'headline' => $subject,
            'greeting' => 'Hello Dr. '.$doctorUser->name.',',
            'message' => $message,
            'details' => $details,
            'action_label' => $actionUrl ? 'Open Dashboard' : null,
            'action_url' => $actionUrl,
        ];

        if ($attachment) {
            $payload = array_merge($payload, $attachment);
        }

        $this->queueTo($doctorUser->email, new SystemAlertMail($payload));
    }

    protected function resolveAdminUsersForHospital(string $hospitalId): Collection
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Admin', 'Super Admin']);
            })
            ->where(function ($query) use ($hospitalId) {
                $query->where('hospital_id', $hospitalId)
                    ->orWhereHas('hospital', function ($hospitalQuery) use ($hospitalId) {
                        $hospitalQuery->where('id', $hospitalId);
                    })
                    ->orWhereHas('doctor', function ($doctorQuery) use ($hospitalId) {
                        $doctorQuery->where('hospital_id', $hospitalId);
                    });
            })
            ->get();
    }

    protected function queueTo(string|array|null $to, object $mailable): void
    {
        if (empty($to)) {
            return;
        }

        try {
            Mail::to($to)->queue($mailable);
        } catch (\Throwable $exception) {
            Log::error('Failed to queue email notification', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
