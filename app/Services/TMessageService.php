<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * TMessageService - Handles TMessage (Patient Messages) model operations
 */
class TMessageService extends BaseService
{
    protected string $auditModule = 'TMessage';

    /**
     * Get TMessages with filtering and pagination
     */
    public function list($request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $doctor = Auth::user()->doctor;

        return TMessage::with(['patient', 'doctor'])
            ->when($request->patient_id, fn ($q, $pid) => $q->where('patient_id', $pid))
            ->when($request->search, fn ($q, $keyword) => $q->where(fn ($q2) => $q2
                ->where('subject', 'like', "%{$keyword}%")
                ->orWhere('message', 'like', "%{$keyword}%")
                ->orWhereHas('patient.user', fn ($q3) => $q3->where('name', 'like', "%{$keyword}%"))
            ))
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();
    }

    /**
     * Get patients for dropdown
     */
    public function getPatients($doctor): \Illuminate\Database\Eloquent\Collection
    {
        return Patient::with('user')
            ->whereHas('doctorPatients', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->get();
    }

    /**
     * Get doctors for dropdown
     */
    public function getDoctors($doctor): \Illuminate\Database\Eloquent\Collection
    {
        return Doctor::with('user')
            ->where('hospital_id', $doctor->hospital_id)
            ->get();
    }

    /**
     * Create a new TMessage
     */
    public function createMessage(array $data): TMessage
    {
        DB::beginTransaction();

        try {
            $doctor = Auth::user()->doctor;
            $patient = Patient::with('user')->findOrFail($data['patient_id']);

            $senderName = $doctor->user->name ?? 'Doctor';
            $patientName = $patient->user->name ?? 'Unknown';
            $subject = $data['subject'].' [RE: '.$patientName.']';
            $status = ($data['submit_type'] ?? null) === 'draft' ? 'Draft' : 'Sent';

            $tMessage = TMessage::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $doctor->id,
                'hospital_id' => $doctor->hospital_id,
                'to' => $data['to'] ?? null,
                'from' => $doctor->user->id,
                'messages_signed' => $data['messages_signed'] ?? null,
                'date' => $data['date'],
                'messages_dos' => $data['messages_dos'] ?? null,
                'subject' => $subject,
                'message' => $data['message'],
            ]);

            $this->logCreate($tMessage, "Created TMessage: {$subject}");

            if ($status === 'Sent' && ! empty($data['to'])) {
                $this->sendMessageEmails($tMessage, $data, $senderName, $patientName);
            }

            DB::commit();

            return $tMessage;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('TMessage create error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Send message emails to recipients
     */
    protected function sendMessageEmails(TMessage $tMessage, array $data, string $senderName, string $patientName): void
    {
        if (empty($data['to'])) {
            return;
        }

        $recipientIds = $this->parseRecipients($data['to']);
        $recipients = User::whereIn('id', $recipientIds)->get()->keyBy('id');

        foreach ($recipientIds as $recipientId) {
            if (! isset($recipients[$recipientId])) {
                continue;
            }

            $recipient = $recipients[$recipientId];

            DB::table('messaging')->insert([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $tMessage->doctor_id,
                'hospital_id' => $tMessage->hospital_id,
                'message_to' => $data['to'],
                'message_from' => $tMessage->doctor->user->email,
                'subject' => $tMessage->subject,
                'message' => $data['message'],
                't_messages_id' => $tMessage->id,
                'status' => 'Sent',
                'mailbox' => $recipientId,
            ]);

            if ($recipient->hasRole('patient') && $recipient->email) {
                $this->sendMail($recipient->email, $tMessage->subject, $data['message'], $patientName, $senderName);
            }
        }

        $toEmails = $recipients->pluck('email')->filter()->toArray();

        if (! empty($toEmails)) {
            Mail::to($toEmails)->send(new \App\Mail\MessageMail([
                'subject' => $tMessage->subject,
                'message' => $data['message'],
                'patient_name' => $patientName,
                'sender_name' => $senderName,
            ]));
        }
    }

    /**
     * Get TMessage by ID
     */
    public function getMessage(string $id): TMessage
    {
        $message = TMessage::with(['patient.user', 'doctor.user'])->findOrFail($id);
        $this->logView($message, "Viewed TMessage: {$message->subject}");

        return $message;
    }

    /**
     * Update an existing TMessage
     */
    public function updateMessage(array $data, string $id): TMessage
    {
        DB::beginTransaction();

        try {
            $tMessage = TMessage::findOrFail($id);
            $oldMessage = clone $tMessage;

            $patient = Patient::with('user')->findOrFail($data['patient_id']);

            $subject = $data['subject'];
            if ($patient->user) {
                $subject .= ' [RE: '.$patient->user->name.']';
            }

            $tMessage->update([
                'patient_id' => $data['patient_id'],
                'to' => $data['to'],
                'from' => $data['from'] ?? $tMessage->from,
                'messages_signed' => $data['messages_signed'],
                'date' => $data['date'],
                'messages_dos' => $data['messages_dos'],
                'subject' => $subject,
                'message' => $data['message'],
            ]);

            $this->logUpdate($oldMessage, $tMessage, "Updated TMessage: {$subject}");

            DB::commit();

            return $tMessage;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('TMessage update error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a TMessage
     */
    public function deleteMessage(string $id): void
    {
        $tMessage = TMessage::findOrFail($id);
        $this->logDelete($tMessage, "Deleted TMessage: {$tMessage->subject}");
        $tMessage->delete();
    }

    /**
     * Parse recipients from string or array
     */
    protected function parseRecipients(mixed $recipients): array
    {
        if (is_array($recipients)) {
            return $recipients;
        }

        return array_filter(array_map('trim', preg_split('/[,;]/', (string) $recipients)));
    }

    /**
     * Helper: Send mail
     */
    protected function sendMail(string $email, string $subject, string $message, string $patientName, string $senderName): void
    {
        Mail::to($email)->send(new \App\Mail\MessageMail([
            'subject' => $subject,
            'message' => $message,
            'patient_name' => $patientName,
            'sender_name' => $senderName,
        ]));
    }
}
