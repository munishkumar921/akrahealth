<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentCancelled extends Notification implements ShouldQueue
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
            'type' => 'appointment_cancelled',
            'action_url' => route('appointments.index'), // Or a specific 'cancelled' view
        ];
    }

    public function toMail(object $notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Appointment Cancelled')
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
            ->salutation('Best regards, AKRA Health Team');
    }

    private function getMessage($notifiable): string
    {
        $patientName = $this->appointment->patient->name ?? 'the patient';
        $doctorName = $this->appointment->doctor->user->name ?? 'the doctor';
        $appointmentDateTime = $this->appointment->appointment_date.' at '.$this->appointment->appointment_time;

        // Message for the patient
        if ($notifiable->id === $this->appointment->patient?->user_id) {
            return "Your appointment with {$doctorName} on {$appointmentDateTime} has been cancelled.";
        }

        // Message for the doctor
        if ($notifiable->id === $this->appointment->doctor?->user_id) {
            return "The appointment with {$patientName} on {$appointmentDateTime} has been cancelled.";
        }

        // Default/Admin message
        return "An appointment between {$doctorName} and {$patientName} for {$appointmentDateTime} has been cancelled.";
    }
}
