<?php

namespace App\Mail;

use App\Models\ShowroomItem;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $customer, public ShowroomItem $item) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your demo access is approved: ' . $this->item->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.access-approved');
    }
}
