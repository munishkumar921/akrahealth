<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentPaymentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Payment Reminder for Your Upcoming Appointment')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('This is a reminder that payment is still pending for your upcoming appointment.')
            ->line('Please complete the payment to secure your booking.')
            ->line('Appointment Details:')
            ->line('**Date:** '.$this->appointment->appointment_date->format('l, F j, Y'))
            ->line('**Time:** '.$this->appointment->appointment_time)
            ->line('**Type:** '.$this->appointment->appointment_type)
            ->line('**Amount:** '.($this->appointment->currency ?? 'USD').' '.number_format($this->appointment->total_amount ?? $this->appointment->fee_amount, 2))
            ->when($this->appointment->doctor, function ($mail) {
                return $mail->line('**Doctor:** Dr. '.$this->appointment->doctor->user->name);
            })
            ->when($this->appointment->reason, function ($mail) {
                return $mail->line('**Reason:** '.$this->appointment->reason);
            })
            ->action('Pay Now', route('patient.appointment.payment', $this->appointment->id))
            ->salutation('Best regards, AKRA Health Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'appointment_date' => $this->appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => $this->appointment->appointment_time,
            'appointment_type' => $this->appointment->appointment_type,
            'doctor_name' => $this->appointment->doctor ? $this->appointment->doctor->user->name : null,
            'patient_name' => $this->appointment->patient ? $this->appointment->patient->user->name : null,
            'reason' => $this->appointment->reason,
            'total_amount' => $this->appointment->total_amount ?? $this->appointment->fee_amount,
            'currency' => $this->appointment->currency ?? 'USD',
            'message' => 'Payment reminder: Please complete payment for your upcoming appointment.',
            'type' => 'payment_reminder',
            'action_url' => route('patient.appointment.payment', $this->appointment->id),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
