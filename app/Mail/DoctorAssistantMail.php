<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorAssistantMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = isset($this->data['subject']) ? $this->data['subject'] : 'Doctor Assistant Mail';

        return new Envelope(
            subject: $subject,
        );
    }

    public function build()
    {
        $subject = isset($this->data['subject']) ? $this->data['subject'] : 'Assistant HireUs';

        return $this->view('emails.doctor_assistant')->subject($subject);

    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
