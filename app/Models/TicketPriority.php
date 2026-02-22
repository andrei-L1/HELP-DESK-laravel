<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketPriority extends Model
{
    use HasFactory, SoftDeletes;

    // Constants for magic values
    const LOW = 1;
    const NORMAL = 2;
    const HIGH = 3;
    const URGENT = 4;

    protected $fillable = [
        'name',
        'title',
        'description',
        'sort_order',
    ];

    /**
     * Get the tickets for this priority.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'priority_id');
    }
}
