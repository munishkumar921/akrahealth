<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentPaymentReceived extends Notification
{
    use Queueable;

    private $appointment;

    /**
     * Create a new notification instance.
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $doctorName = $this->appointment->doctor->user->name ?? '';
        $patientName = $this->appointment->patient->user->name ?? '';
        $appointmentDate = Carbon::parse($this->appointment->appointment_date)->format('F j, Y, g:i a');

        return (new MailMessage)
            ->line("Dear Dr. {$doctorName}")
            ->line("You have received payment for an appointment with {$patientName} on {$appointmentDate}.")
            ->line('Please log in to your dashboard for more details.')
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
