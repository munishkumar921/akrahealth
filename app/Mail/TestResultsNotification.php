<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestResultsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $portal;

    public $displayname;

    public $email;

    public $patient_portal;

    /**
     * Create a new message instance.
     */
    public function __construct($portal, $displayname, $email, $patient_portal)
    {
        $this->portal = $portal;
        $this->displayname = $displayname;
        $this->email = $email;
        $this->patient_portal = $patient_portal;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Test Results Available')
            ->view('emails.test-results-notification');
    }
}
