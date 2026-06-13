<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'created_by', 'number', 'description', 'work_summary', 'amount',
        'status', 'issued_at', 'due_at', 'paid_at', 'file_path', 'notes',
        'visible_to_customer',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'issued_at'           => 'date',
        'due_at'              => 'date',
        'paid_at'             => 'date',
        'visible_to_customer' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** True when unpaid and past its due date. */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid'
            && $this->due_at !== null
            && $this->due_at->isPast();
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'paid'    => 'badge-green',
            'sent'    => 'badge-blue',
            'overdue' => 'badge-red',
            default   => 'badge-muted', // draft
        };
    }
}
