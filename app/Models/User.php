<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

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
    ];

    // If you kept 'password_hash' instead of 'password':
    // public function getAuthPassword()
    // {
    //     return $this->password_hash;
    // }

    public function role()
    {
        return $this->belongsTo(Role::class);  // assuming you have a Role model
    }
}