<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MedicationPrescribed extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public $link;

    public function __construct($data, $link)
    {
        $this->data = $data;
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('New Medication Prescribed')
            ->view('emails.medication-prescribed')
            ->with([
                'message' => $this->data,
                'link' => $this->link,
            ]);
    }
}
