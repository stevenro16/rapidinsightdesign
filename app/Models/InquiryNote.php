<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryNote extends Model
{
    protected $fillable = ['inquiry_id', 'author_id', 'body', 'visible_to_customer'];

    protected $casts = ['visible_to_customer' => 'boolean'];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
