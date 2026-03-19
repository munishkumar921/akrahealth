<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public $patient;

    public $doctor;

    public $provider;

    public function __construct($order, $patient, $doctor, $provider = null)
    {
        $this->order = $order;
        $this->patient = $patient;
        $this->doctor = $doctor;
        $this->provider = $provider;
    }

    public function build()
    {
        return $this->subject('New Order Placed')
            ->view('emails.order-notification')
            ->with([
                'order' => $this->order,
                'patient' => $this->patient,
                'doctor' => $this->doctor,
            ]);
    }
}
