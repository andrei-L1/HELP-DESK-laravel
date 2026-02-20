<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'middle_name',
        'display_name',
        'role_id',
        'phone',
        'avatar_url',
        'timezone',
        'is_active',
        'email_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'is_active'      => 'boolean',
        'last_login'     => 'datetime',
        'role_id'        => 'integer',          // ← added (helps with comparisons)
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_login',
        'deleted_at',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Optional but very useful helpers
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role?->name === 'manager';
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    // Optional: scope for active users
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}