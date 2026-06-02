<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'company', 'notes',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool  { return $this->role === 'admin'; }
    public function isStaff(): bool  { return $this->role === 'staff'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }

    public function isStaffOrAdmin(): bool { return in_array($this->role, ['admin', 'staff']); }

    public function showroomItems(): BelongsToMany
    {
        return $this->belongsToMany(ShowroomItem::class, 'customer_showroom_access')
            ->withPivot(['granted_by', 'granted_at'])
            ->withTimestamps();
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }
}
