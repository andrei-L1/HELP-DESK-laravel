<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlaPolicy extends Model
{
    use SoftDeletes;

    protected $table = 'sla_policies';

    // Enable timestamps
    public $timestamps = true;

    protected $fillable = [
        'name', 
        'description',          
        'priority_id', 
        'department_id',
        'response_time', 
        'resolution_time',
        'escalate_after',        
        'notify_before',         
        'notify_escalation',     
        'is_active',
        'business_hours_only',   
        'calendar_id',           
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'notify_escalation'   => 'boolean',
        'business_hours_only' => 'boolean',
        'response_time'       => 'integer',
        'resolution_time'     => 'integer',
        'escalate_after'      => 'integer',
        'notify_before'       => 'integer',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    // Optional: Add relationships
    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'sla_policy_id');
    }
}