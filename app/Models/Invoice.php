<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'agreement_id', 'created_by', 'number', 'description', 'work_summary',
        'amount', 'subtotal', 'tax_rate', 'tax_amount',
        'status', 'issued_at', 'due_at', 'paid_at', 'file_path', 'notes',
        'visible_to_customer',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'subtotal'            => 'decimal:2',
        'tax_rate'            => 'decimal:2',
        'tax_amount'          => 'decimal:2',
        'issued_at'           => 'date',
        'due_at'              => 'date',
        'paid_at'             => 'date',
        'visible_to_customer' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Auto-assign a unique sequential invoice number when none is provided.
        static::creating(function (self $invoice) {
            if (blank($invoice->number)) {
                $invoice->number = self::generateNumber();
            }
        });
    }

    /** Next free invoice number, e.g. INV-0001. */
    public static function generateNumber(): string
    {
        $seq = (int) (self::max('id') ?? 0) + 1;

        do {
            $number = 'INV-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $seq++;
        } while (self::where('number', $number)->exists());

        return $number;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(InvoiceEvent::class)->latest();
    }

    /** Recompute subtotal/tax/total from the line items + tax rate, and persist. */
    public function recalcTotals(): void
    {
        $subtotal  = $this->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
        $taxAmount = round($subtotal * ((float) $this->tax_rate / 100), 2);

        $this->forceFill([
            'subtotal'   => $subtotal,
            'tax_amount' => $taxAmount,
            'amount'     => $subtotal + $taxAmount,
        ])->save();
    }

    public function logEvent(string $action, string $description, ?int $userId = null, ?array $meta = null): void
    {
        $this->events()->create([
            'user_id'     => $userId ?? auth()->id(),
            'action'      => $action,
            'description' => $description,
            'meta'        => $meta,
        ]);
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
