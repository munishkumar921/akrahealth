<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Lab;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\User;
use App\Notifications\SystemDatabaseNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class InAppNotificationService
{
    public function notifySuperAdmins(array $payload): void
    {
        $superAdmins = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'SuperAdmin');
            })
            ->where('is_active', true)
            ->get();

        $this->notifyUsers($superAdmins, array_merge($payload, [
            'recipient_role' => 'SuperAdmin',
            'channel' => $payload['channel'] ?? 'in_app',
        ]));
    }

    public function notifyUser(?User $user, array $payload): void
    {
        if (! $user) {
            return;
        }

        $user->notify(new SystemDatabaseNotification($this->normalizePayload($payload)));
    }

    public function notifyUsers(iterable $users, array $payload): void
    {
        $normalizedPayload = $this->normalizePayload($payload);

        $this->normalizeUsers($users)
            ->unique('id')
            ->each(function (User $user) use ($normalizedPayload) {
                $user->notify(new SystemDatabaseNotification($normalizedPayload));
            });
    }

    public function notifyDoctor(Doctor|string|null $doctor, array $payload): void
    {
        $doctorModel = $doctor instanceof Doctor ? $doctor : Doctor::with('user')->find($doctor);
        $this->notifyUser($doctorModel?->user, $payload);
    }

    public function notifyPatient(Patient|string|null $patient, array $payload): void
    {
        $patientModel = $patient instanceof Patient ? $patient : Patient::with('user')->find($patient);
        $this->notifyUser($patientModel?->user, $payload);
    }

    public function notifyLab(Lab|string|null $lab, array $payload): void
    {
        $labModel = $lab instanceof Lab ? $lab : Lab::with('user')->find($lab);
        $this->notifyUser($labModel?->user, $payload);
    }

    public function notifyPharmacy(Pharmacy|string|null $pharmacy, array $payload): void
    {
        $pharmacyModel = $pharmacy instanceof Pharmacy ? $pharmacy : Pharmacy::with('user')->find($pharmacy);
        $this->notifyUser($pharmacyModel?->user, $payload);
    }

    public function notifyAdminsForHospital(?string $hospitalId, array $payload): void
    {
        if (! $hospitalId) {
            return;
        }

        $admins = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Admin');
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

        $this->notifyUsers($admins, $payload);
    }

    public function buildPayload(
        string $title,
        string $message,
        string $type,
        array $extra = []
    ): array {
        return $this->normalizePayload(array_merge($extra, [
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]));
    }

    private function normalizePayload(array $payload): array
    {
        return array_filter([
            'title' => $payload['title'] ?? 'Notification',
            'message' => $payload['message'] ?? '',
            'type' => $payload['type'] ?? 'system',
            'channel' => $payload['channel'] ?? 'in_app',
            'recipient_role' => $payload['recipient_role'] ?? null,
            'action_url' => $payload['action_url'] ?? null,
            'related_model_type' => $payload['related_model_type'] ?? null,
            'related_model_id' => $payload['related_model_id'] ?? null,
            'appointment_id' => $payload['appointment_id'] ?? null,
            'order_id' => $payload['order_id'] ?? null,
            'invoice_id' => $payload['invoice_id'] ?? null,
            'encounter_id' => $payload['encounter_id'] ?? null,
            'form_id' => $payload['form_id'] ?? null,
            'document_id' => $payload['document_id'] ?? null,
            'prescription_id' => $payload['prescription_id'] ?? null,
            'pharmacy_order_id' => $payload['pharmacy_order_id'] ?? null,
            'lab_order_id' => $payload['lab_order_id'] ?? null,
            'patient_id' => $payload['patient_id'] ?? null,
            'doctor_id' => $payload['doctor_id'] ?? null,
            'hospital_id' => $payload['hospital_id'] ?? null,
            'meta' => $payload['meta'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function normalizeUsers(iterable $users): Collection
    {
        if ($users instanceof EloquentCollection) {
            return $users->filter(fn ($user) => $user instanceof User)->values();
        }

        return collect($users)->filter(fn ($user) => $user instanceof User)->values();
    }
}
