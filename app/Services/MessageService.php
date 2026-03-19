<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Message;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * MessageService - Handles Message model operations
 */
class MessageService extends BaseService
{
    protected string $auditModule = 'Message';

    /**
     * Get messages with filtering and pagination
     */
    public function list($request)
    {
        $hospital = Auth::user()->hospital->id;

        return Message::with(['patient', 'doctor.user'])
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
     * Create a new message
     */
    public function createMessage(array $data): Message
    {
        DB::beginTransaction();

        try {
            $doctor = Auth::user()->doctor;
            $patient = Patient::with('user')->findOrFail($data['patient_id']);

            $senderName = $doctor->user->name ?? 'Doctor';
            $patientName = $patient->user->name ?? 'Unknown';
            $subject = $data['subject'].' [RE: '.$patientName.']';
            $status = ($data['submit_type'] ?? null) === 'draft' ? 'Draft' : 'Sent';

            $message = Message::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $doctor->id,
                'hospital_id' => $doctor->hospital_id,
                'from' => $doctor->user->id,
                'to' => $this->implodeRecipients($data['to'] ?? []),
                'cc' => $this->implodeRecipients($data['cc'] ?? []),
                'subject' => $subject,
                'message' => $data['message'],
                'date' => $data['date'],
                'status' => $status,
                'mailbox' => $doctor->user_id,
                'read' => false,
            ]);

            $this->logCreate($message, "Created message: {$subject}");

            if ($status === 'Sent' && ! empty($data['to'])) {
                $this->sendMessageEmails($message, $data, $senderName, $patientName);
            }

            if (! empty($data['t_messages_id'])) {
                $this->appendToThread($data['t_messages_id'], $data['message'], $senderName);
            }

            DB::commit();

            return $message;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Message create error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Send message emails to recipients
     */
    protected function sendMessageEmails(Message $message, array $data, string $senderName, string $patientName): void
    {
        if (empty($data['to'])) {
            return;
        }

        $recipients = User::whereIn('id', $data['to'])->get()->keyBy('id');
        $toString = $this->implodeRecipients($data['to']);
        $ccString = $this->implodeRecipients($data['cc'] ?? []);

        foreach ($data['to'] as $recipientId) {
            if (! isset($recipients[$recipientId])) {
                continue;
            }

            $recipient = $recipients[$recipientId];

            DB::table('messages')->insert([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $message->doctor_id,
                'hospital_id' => $message->hospital_id,
                'message_to' => $toString,
                'message_from' => $message->doctor->user->email,
                'subject' => $message->subject,
                'message' => $data['message'],
                't_messages_id' => $message->id,
                'status' => 'Sent',
                'mailbox' => $recipientId,
                'cc' => $ccString,
            ]);

            if ($recipient->hasRole('patient') && $recipient->email) {
                $this->sendMail($recipient->email, $message->subject, $data['message'], $patientName, $senderName);
            }
        }

        $toEmails = $recipients->pluck('email')->filter()->toArray();
        $ccEmails = $this->getCcEmails($data['cc'] ?? []);

        if (! empty($toEmails)) {
            $mail = Mail::to($toEmails);
            $mail->when(! empty($ccEmails), fn ($m) => $m->cc($ccEmails));
            $mail->send(new \App\Mail\MessageMail([
                'subject' => $message->subject,
                'message' => $data['message'],
                'patient_name' => $patientName,
                'sender_name' => $senderName,
            ]));
        }
    }

    /**
     * Append reply to message thread
     */
    protected function appendToThread(string $tMessagesId, string $replyMessage, string $senderName): void
    {
        $thread = Message::find($tMessagesId);

        if ($thread) {
            $thread->update([
                'message' => $thread->message.
                    "\n\nOn ".now()->format('Y-m-d H:i').
                    ", {$senderName} wrote:\n".
                    "---------------------------------\n".
                    $replyMessage,
            ]);
        }
    }

    /**
     * Get message by ID
     */
    public function getMessage(string $id): Message
    {
        $message = Message::with(['patient.user', 'doctor.user'])->findOrFail($id);

        if (! $message->read) {
            $message->update(['read' => true, 'read_at' => now()]);
            $this->logUpdate($message, $message->fresh(), 'Marked as read');
        }

        return $message;
    }

    /**
     * Update an existing message
     */
    public function updateMessage(array $data, string $id): Message
    {
        DB::beginTransaction();

        try {
            $message = Message::findOrFail($id);
            $oldMessage = clone $message;

            $doctor = Auth::user()->doctor;
            $patient = Patient::with('user')->findOrFail($data['patient_id']);

            $status = match ($data['submit_type'] ?? '') {
                'draft' => 'Draft',
                'sent' => 'Sent',
                default => $message->status,
            };

            $subject = $data['subject'];
            if ($patient->user) {
                $subject .= ' [RE: '.$patient->user->name.']';
            }

            $message->update([
                'patient_id' => $data['patient_id'],
                'subject' => $subject,
                'message' => $data['message'],
                'date' => $data['date'],
                'from' => $data['from'] ?? $message->from,
                'to' => $this->formatRecipients($data['to'] ?? ''),
                'cc' => $this->formatRecipients($data['cc'] ?? ''),
                'status' => $status,
            ]);

            $this->logUpdate($oldMessage, $message, "Updated message: {$subject}");

            if ($status === 'Sent' && ! empty($data['to'])) {
                $this->sendMessageEmails($message, $data, $doctor->user->name ?? 'Doctor', $patient->user->name ?? 'Unknown');
            }

            DB::commit();

            return $message;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Message update error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage(string $id): void
    {
        $message = Message::findOrFail($id);
        $this->logDelete($message, "Deleted message: {$message->subject}");
        $message->delete();
    }

    /**
     * Get messages for a specific patient
     */
    public function getPatientMessages(string $patientId): \Illuminate\Database\Eloquent\Collection
    {
        return Message::with(['patient.user', 'doctor.user'])
            ->where('doctor_id', Auth::user()->doctor->id)
            ->where('patient_id', $patientId)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $id): Message
    {
        $message = Message::findOrFail($id);
        $oldMessage = clone $message;
        $message->update(['read' => true, 'read_at' => now()]);
        $this->logUpdate($oldMessage, $message, 'Marked as read');

        return $message;
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(): int
    {
        return Message::where('doctor_id', Auth::user()->doctor->id)
            ->where('read', false)
            ->count();
    }

    /**
     * Helper: Convert array to semicolon-separated string
     */
    protected function implodeRecipients(array $recipients): ?string
    {
        return ! empty($recipients) ? implode(';', $recipients) : null;
    }

    /**
     * Helper: Format recipients (array or string)
     */
    protected function formatRecipients(mixed $recipients): string
    {
        if (empty($recipients)) {
            return '';
        }

        return is_array($recipients) ? implode(';', $recipients) : $recipients;
    }

    /**
     * Helper: Get CC email addresses
     */
    protected function getCcEmails(array $ccIds): array
    {
        if (empty($ccIds)) {
            return [];
        }

        return User::whereIn('id', $ccIds)->pluck('email')->filter()->toArray();
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
