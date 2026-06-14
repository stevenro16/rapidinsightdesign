<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'agreement_id', 'user_id', 'amount', 'type', 'status',
        'method', 'gateway', 'reference', 'paid_at', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'confirmed' => 'badge-green',
            'pending'   => 'badge-amber',
            'refunded'  => 'badge-blue',
            'failed'    => 'badge-red',
            default     => 'badge-muted',
        };
    }
}
