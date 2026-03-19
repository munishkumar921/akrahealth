<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JoinCallNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $appointment;

    public $link;

    public function __construct($appointment, $link)
    {
        $this->appointment = $appointment;
        $this->link = $link;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Join Your Video Consultation')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your doctor has started the video call for your appointment.')
            ->action('Join Call Now', $this->link)
            ->line('Please click the button above to join the session immediately.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'message' => 'Your doctor is waiting. Join the call now.',
            'link' => $this->link,
            'type' => 'video_call_invite',
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'message' => 'Your doctor is waiting. Join the call now.',
            'link' => $this->link,
            'type' => 'video_call_invite',
        ];
    }
}
