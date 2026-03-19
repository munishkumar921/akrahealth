<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;

class RazorpayInvoiceMail extends Mailable
{
    public $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $customerDetails = $this->invoice->customer_details ?? [];
        $customerEmail = $customerDetails['email'] ?? null;
        $customerName = $customerDetails['name'] ?? 'Customer';

        // Generate payment URL if invoice is not paid
        $paymentUrl = null;
        if (! $this->invoice->isPaid()) {
            $paymentUrl = URL::signedRoute('invoice.pay', [
                'invoice' => $this->invoice->id,
            ]);
        }

        return $this->to($customerEmail, $customerName)
            ->subject('Invoice '.$this->invoice->invoice_number.' from AKRA Health')
            ->view('emails.razorpay_invoice', [
                'invoice' => $this->invoice,
                'paymentUrl' => $paymentUrl,
            ]);
    }
}
