<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    // Table name (optional if it matches 'permissions')
    protected $table = 'permissions';

    // Fillable fields for mass assignment
    protected $fillable = [
        'name',
        'title',
        'category',
    ];

    /**
     * Roles that belong to this permission
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}