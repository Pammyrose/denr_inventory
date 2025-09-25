<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    // Optional: Direct relationship to employee via email (for easier querying)
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'email', 'email');
    }

    // Accessor for full_name (matches Profile.vue)
    public function getFullNameAttribute(): string 
    {
        return $this->name ?? 'Unknown User';
    }

    // Accessor for role (matches Profile.vue)
    public function getRoleAttribute(): string
    {
        return $this->is_admin ? 'Admin' : 'User';
    }

}

