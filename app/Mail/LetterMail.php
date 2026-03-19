<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $patientName;

    public $doctorName;

    public $letterType;

    public $subject;

    public $body;

    public $pdfContent;

    public $fileName;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $patientName,
        string $doctorName,
        string $letterType,
        string $subject,
        string $body,
        string $pdfContent,
        string $fileName
    ) {
        $this->patientName = $patientName;
        $this->doctorName = $doctorName;
        $this->letterType = $letterType;
        $this->subject = $subject;
        $this->body = $body;
        $this->pdfContent = $pdfContent;
        $this->fileName = $fileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Medical Letter: '.$this->subject)
            ->view('emails.letter')
            ->attachData($this->pdfContent, $this->fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
