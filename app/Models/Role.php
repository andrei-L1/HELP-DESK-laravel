<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'description',
        'is_system',
        'sort_order',
    ];

    /**
     * Permissions assigned to this role
     */
    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class, 'role_permissions');
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }

    /**
     * Users assigned to this role
     */
    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}