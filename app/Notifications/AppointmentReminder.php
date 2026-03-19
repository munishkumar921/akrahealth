<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment, public string $recipientType = 'patient')
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Appointment Reminder - '.$this->appointment->appointment_date->format('M d, Y'))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('This is a reminder for your upcoming appointment.')
            ->line('Appointment Details:')
            ->line('**Date:** '.$this->appointment->appointment_date->format('l, F j, Y'))
            ->line('**Time:** '.$this->appointment->appointment_time)
            ->line('**Type:** '.$this->appointment->appointment_type)
            ->when($this->appointment->doctor, function ($mail) {
                return $mail->line('**Doctor:** Dr. '.$this->appointment->doctor->user->name);
            })
            ->when($this->appointment->reason, function ($mail) {
                return $mail->line('**Reason:** '.$this->appointment->reason);
            })
            ->action('View Appointment', route('patient.appointments'))
            ->salutation('Best regards, AKRA Health Team');
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->recipientType === 'doctor'
            ? 'You have an upcoming appointment with a patient.'
            : 'You have an upcoming appointment reminder.';

        return [
            'appointment_id' => $this->appointment->id,
            'appointment_date' => $this->appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => $this->appointment->appointment_time,
            'appointment_type' => $this->appointment->appointment_type,
            'doctor_name' => $this->appointment->doctor ? $this->appointment->doctor->user->name : null,
            'patient_name' => $this->appointment->patient ? $this->appointment->patient->name : null,
            'reason' => $this->appointment->reason,
            'recipient_type' => $this->recipientType,
            'message' => $message,
        ];
    }
}
