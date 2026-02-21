<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlaPolicy extends Model
{
    use SoftDeletes;

    protected $table = 'sla_policies';

    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'name', 'priority_id', 'department_id',
        'response_time', 'resolution_time', 'is_active',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'response_time'   => 'integer',
        'resolution_time' => 'integer',
    ];
}
