<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentStatusUpdated extends Notification implements ShouldQueue
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
            'status' => $this->appointment->status,
            'appointment_date' => $this->appointment->appointment_date,
            'appointment_time' => $this->appointment->appointment_time,
            'doctor_name' => $this->appointment->doctor->user->name ?? 'Unknown Doctor',
            'patient_name' => $this->appointment->patient->name ?? 'Unknown Patient',

            'message' => $this->getMessage($notifiable),

            'for_role' => $this->forRole,
            'type' => 'appointment_status_updated',
            'action_url' => route('appointments.show', $this->appointment->id),
        ];
    }

    public function toMail(object $notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Appointment Status Updated')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line($this->getMessage($notifiable))
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
            ->action('View Appointment', route('appointments.show', $this->appointment->id))
            ->salutation('Best regards, AKRA Health Team');
    }

    private function getMessage($notifiable)
    {
        if ($notifiable->id === $this->appointment->patient?->user_id) {
            return "Your appointment status has been updated to {$this->appointment->status}.";
        }

        if ($notifiable->id === $this->appointment->doctor?->user_id) {
            return "{$this->appointment->patient->name} is now {$this->appointment->status}.";
        }

        return "Appointment status changed to {$this->appointment->status}.";
    }
}
