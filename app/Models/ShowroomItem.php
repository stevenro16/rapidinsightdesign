<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class ShowroomItem extends Model
{
    protected $fillable = [
        'title', 'description', 'embed_url', 'thumbnail_path',
        'tech_tags', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_showroom_access')
            ->withPivot(['granted_by', 'granted_at'])
            ->withTimestamps();
    }

    public function techTagsArray(): array
    {
        return $this->tech_tags ? explode(',', $this->tech_tags) : [];
    }
}
