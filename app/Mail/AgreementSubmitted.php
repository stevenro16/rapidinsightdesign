<?php

namespace App\Mail;

use App\Models\Agreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgreementSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Agreement $agreement) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Agreement signed & submitted: ' . $this->agreement->title,
            replyTo: [new Address($this->agreement->customer->email, $this->agreement->customer->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.agreement-submitted');
    }
}
