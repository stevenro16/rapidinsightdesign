<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class ShowroomItem extends Model
{
    protected $fillable = [
        'title', 'description', 'embed_url', 'public_url', 'private_url',
        'preview_html_path', 'preview_url', 'preview_mode',
        'thumbnail_path', 'tech_tags', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(ShowcaseSlide::class)->orderBy('sort_order');
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

    /**
     * Resolved public preview source — an explicit URL takes precedence over an
     * uploaded HTML file. Returns null when no preview has been configured.
     */
    public function previewUrl(): ?string
    {
        if ($this->preview_url) {
            return $this->preview_url;
        }

        return $this->preview_html_path ? Storage::url($this->preview_html_path) : null;
    }

    public function previewMode(): string
    {
        return $this->preview_mode ?: 'frame';
    }

    public function hasPreview(): bool
    {
        return $this->previewUrl() !== null;
    }
}
