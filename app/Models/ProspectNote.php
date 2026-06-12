<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectNote extends Model
{
    protected $fillable = ['prospect_id', 'body'];

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }
}
