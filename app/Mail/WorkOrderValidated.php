<?php

namespace App\Mail;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderValidated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WorkOrder $workOrder) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Work order validated by customer: ' . $this->workOrder->title,
            replyTo: [new Address($this->workOrder->customer->email, $this->workOrder->customer->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.work-order-validated');
    }
}
