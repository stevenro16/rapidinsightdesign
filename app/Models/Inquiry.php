<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Inquiry extends Model
{
    protected $fillable = [
        'user_id', 'name', 'email', 'subject', 'message', 'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'new'         => 'badge-green',
            'in_progress' => 'badge-amber',
            'resolved'    => 'badge-muted',
            default       => 'badge-muted',
        };
    }
}
