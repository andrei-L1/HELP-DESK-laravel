<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketStatus extends Model
{
    use HasFactory, SoftDeletes;

    // Constants for magic values
    const OPEN = 1;
    const IN_PROGRESS = 2;
    const PENDING = 3;
    const RESOLVED = 4;
    const CLOSED = 5;

    protected $fillable = [
        'name',
        'title',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the tickets for this status.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'status_id');
    }
}
