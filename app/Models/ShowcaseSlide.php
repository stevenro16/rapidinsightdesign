<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowcaseSlide extends Model
{
    protected $fillable = [
        'showroom_item_id', 'title', 'headline', 'description',
        'bullets', 'image_path', 'sort_order',
    ];

    protected $casts = [
        'bullets' => 'array',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShowroomItem::class, 'showroom_item_id');
    }
}
