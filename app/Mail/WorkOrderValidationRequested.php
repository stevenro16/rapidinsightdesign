<?php

namespace App\Mail;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkOrderValidationRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WorkOrder $workOrder) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Please review your work order: ' . $this->workOrder->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.work-order-validation');
    }
}
