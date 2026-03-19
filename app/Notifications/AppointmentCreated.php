<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment, public string $forRole)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'appointment_date' => $this->appointment->appointment_date,
            'appointment_time' => $this->appointment->appointment_time,
            'doctor_name' => $this->appointment->doctor->user->name ?? 'Unknown Doctor',
            'patient_name' => $this->appointment->patient->name ?? 'Unknown Patient',
            'appointment_type' => $this->appointment->appointment_type,
            'status' => $this->appointment->status,

            // 🔥 Role-based message
            'message' => $this->getCustomMessage($notifiable),

            // 🔥 Add role tag for filtering
            'for_role' => $this->forRole,

            'action_url' => route('appointments.show', $this->appointment->id),
            'type' => 'appointment_created',
        ];
    }

    public function toMail(object $notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Appointment '.ucfirst($this->appointment->status))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line($this->getCustomMessage($notifiable))
            ->line('Appointment Details:')
            ->line('**Date:** '.$this->appointment->appointment_date)
            ->line('**Time:** '.$this->appointment->appointment_time)
            ->line('**Type:** '.$this->appointment->appointment_type)
            ->line('**Status:** '.ucfirst($this->appointment->status))
            ->when($this->appointment->doctor, function ($mail) {
                return $mail->line('**Doctor:** Dr. '.$this->appointment->doctor->user->name);
            })
            ->when($this->appointment->patient, function ($mail) {
                return $mail->line('**Patient:** '.$this->appointment->patient->name);
            })
            // ->action('View Appointment', route('appointments.show', $this->appointment->id))
            ->salutation('Best regards, AKRA Health Team');
    }

    private function getCustomMessage($notifiable): string
    {
        if ($notifiable->id === $this->appointment->patient?->user_id) {
            return "Your appointment with Dr. {$this->appointment->doctor->user->name} has been {$this->appointment->status}.";
        }

        if ($notifiable->id === $this->appointment->doctor?->user_id) {
            return "New appointment with {$this->appointment->patient->name} has been scheduled.";
        }

        return "A new appointment has been created ({$this->appointment->appointment_type}).";
    }
}
