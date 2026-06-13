<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerFile extends Model
{
    protected $fillable = ['user_id', 'uploaded_by', 'name', 'path', 'mime', 'size', 'label'];

    protected $casts = ['size' => 'integer'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /** Human-readable file size, e.g. "1.4 MB". */
    public function humanSize(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        $units = ['KB', 'MB', 'GB', 'TB'];
        $i = -1;
        do {
            $bytes /= 1024;
            $i++;
        } while ($bytes >= 1024 && $i < count($units) - 1);

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
