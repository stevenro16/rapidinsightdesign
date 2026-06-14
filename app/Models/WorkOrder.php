<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WorkOrder extends Model
{
    public const STATUSES = ['new', 'in_progress', 'awaiting_customer_validation', 'completed', 'canceled'];

    protected $fillable = [
        'user_id', 'created_by', 'title', 'summary', 'website_url', 'hosting', 'tech_stack', 'details',
        'status', 'customer_validated_at', 'completed_at', 'canceled_at',
    ];

    protected $casts = [
        'customer_validated_at' => 'datetime',
        'completed_at'          => 'datetime',
        'canceled_at'           => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────────────────────── */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class)->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(WorkOrderNote::class)->latest();
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkOrderEvent::class)->latest();
    }

    /** Invoices roll up through the work order's agreements. */
    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Agreement::class);
    }

    /* ── Helpers ───────────────────────────────────────────────────────── */

    public function statusLabel(): string
    {
        return match ($this->status) {
            'in_progress'                  => 'In progress',
            'awaiting_customer_validation' => 'Awaiting validation',
            'completed'                    => 'Completed',
            'canceled'                     => 'Canceled',
            default                        => 'New',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completed'                    => 'badge-green',
            'in_progress'                  => 'badge-blue',
            'awaiting_customer_validation' => 'badge-amber',
            'canceled'                     => 'badge-red',
            default                        => 'badge-muted',
        };
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['completed', 'canceled'], true);
    }

    public function awaitingCustomer(): bool
    {
        return $this->status === 'awaiting_customer_validation';
    }

    public function customerValidated(): bool
    {
        return $this->customer_validated_at !== null;
    }

    /** The most recent note shared with the customer (uses eager-loaded notes when available). */
    public function lastCustomerVisibleNote(): ?WorkOrderNote
    {
        return $this->relationLoaded('notes')
            ? $this->notes->firstWhere('visible_to_customer', true)
            : $this->notes()->where('visible_to_customer', true)->first();
    }

    /** Record an audit-trail event. */
    public function logEvent(string $action, string $description, ?int $userId = null, ?array $meta = null): void
    {
        $this->events()->create([
            'user_id'     => $userId ?? auth()->id(),
            'action'      => $action,
            'description' => $description,
            'meta'        => $meta,
        ]);
    }

    public static function statusLabelFor(string $status): string
    {
        return (new self(['status' => $status]))->statusLabel();
    }
}
