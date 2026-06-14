<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderNote extends Model
{
    protected $fillable = ['work_order_id', 'author_id', 'body', 'visible_to_customer', 'read_at'];

    protected $casts = [
        'visible_to_customer' => 'boolean',
        'read_at'             => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Unread messages that were posted by a customer (for admin notifications). */
    public function scopeUnreadFromCustomers($query)
    {
        return $query->whereNull('read_at')
            ->whereHas('author', fn ($a) => $a->where('role', 'customer'));
    }
}
