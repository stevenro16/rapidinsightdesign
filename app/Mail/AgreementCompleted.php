<?php

namespace App\Mail;

use App\Models\Agreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgreementCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Agreement $agreement) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your agreement is complete');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.agreement-completed');
    }
}
