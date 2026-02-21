<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail, CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, CanResetPasswordTrait;

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
        'email_verified_at', // ← added (for compatibility with Laravel's built-in verification)
        'last_login',       // ← added (for tracking last login time)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'is_active'      => 'boolean',
        'last_login'     => 'datetime',
        'email_verified_at' => 'datetime', // ← added (for compatibility with Laravel's built-in verification)
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
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail());
    }
    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return (bool) $this->email_verified;
    }

    /**
     * Mark the user's email address as verified.
     */
    public function markEmailAsVerified(): bool
    {
        if ($this->email_verified) {
            return false;
        }

        $this->forceFill([
            'email_verified' => true,
            'email_verified_at' => now(), // ← added (for compatibility with Laravel's built-in verification)
        ])->save();

        return true;
    }

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
    public function isAgent(): bool
    {
        return $this->role?->name === 'agent';
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

    /**
     * Check if the user has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->role && $this->role->permissions->contains('name', $permissionName);
    }
}