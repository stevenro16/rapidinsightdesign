<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prospect extends Model
{
    public const STATUSES = ['new', 'shortlisted', 'contacted', 'ruled_out', 'won'];

    protected $fillable = [
        'osm_type', 'osm_id', 'name', 'category', 'lat', 'lng', 'address',
        'phone', 'website', 'email', 'social', 'osm_tags', 'presence_score',
        'status', 'last_synced_at', 'scan_data', 'scanned_at',
    ];

    protected $casts = [
        'social'         => 'array',
        'osm_tags'       => 'array',
        'scan_data'      => 'array',
        'lat'            => 'float',
        'lng'            => 'float',
        'presence_score' => 'integer',
        'last_synced_at' => 'datetime',
        'scanned_at'     => 'datetime',
    ];

    public function notes(): HasMany
    {
        return $this->hasMany(ProspectNote::class)->latest();
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'new'         => 'badge-blue',
            'shortlisted' => 'badge-amber',
            'contacted'   => 'badge-blue',
            'won'         => 'badge-green',
            default       => 'badge-muted',
        };
    }

    public function presenceBand(): string
    {
        return match (true) {
            $this->presence_score < 30 => 'low',
            $this->presence_score < 60 => 'medium',
            default                    => 'high',
        };
    }
}
