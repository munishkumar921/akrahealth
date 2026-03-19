<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentPaymentSuccess extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
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
            ->subject('Payment Successful - Appointment Confirmed')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your payment has been successfully processed.')
            ->line('Appointment Details:')
            ->line('**Date:** '.$this->appointment->appointment_date)
            ->line('**Time:** '.$this->appointment->appointment_time)
            ->line('**Type:** '.$this->appointment->appointment_type)
            ->line('**Amount Paid:** $'.number_format($this->appointment->total_amount, 2))
            ->when($this->appointment->doctor, function ($mail) {
                return $mail->line('**Doctor:** Dr. '.$this->appointment->doctor->user->name);
            })
            ->line('Your appointment is now fully confirmed. You will receive an invoice shortly.')
            ->salutation('Best regards, AKRA Health Team');
    }
}
