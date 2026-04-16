<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function build()
    {
        $mail = $this->subject($this->payload['subject'] ?? 'Akra Health Notification')
            ->view('emails.system-alert');

        if (! empty($this->payload['attachment_path']) && is_string($this->payload['attachment_path']) && file_exists($this->payload['attachment_path'])) {
            $mail->attach(
                $this->payload['attachment_path'],
                [
                    'as' => $this->payload['attachment_name'] ?? basename($this->payload['attachment_path']),
                    'mime' => $this->payload['attachment_mime'] ?? mime_content_type($this->payload['attachment_path']) ?: 'application/octet-stream',
                ]
            );
        }

        return $mail;
    }
}
