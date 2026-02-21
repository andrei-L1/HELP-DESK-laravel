<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'short_code', 'description', 'manager_id', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
