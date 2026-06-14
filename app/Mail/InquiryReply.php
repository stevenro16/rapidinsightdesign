<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryReply extends Mailable
{
    use Queueable, SerializesModels;

    /** @param bool $toCustomer  true = admin replied (notify customer); false = customer replied (notify admin). */
    public function __construct(public Inquiry $inquiry, public string $body, public bool $toCustomer) {}

    public function envelope(): Envelope
    {
        if ($this->toCustomer) {
            return new Envelope(subject: 'New reply to your inquiry: ' . $this->inquiry->subject);
        }

        return new Envelope(
            subject: 'New reply on inquiry: ' . $this->inquiry->subject,
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.inquiry-reply');
    }
}
