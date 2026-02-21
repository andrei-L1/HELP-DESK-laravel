<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivityLog extends Model
{
    use SoftDeletes;

    protected $fillable = ['ticket_id', 'user_id', 'action', 'old_value', 'new_value', 'details'];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];

    const UPDATED_AT = null;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
