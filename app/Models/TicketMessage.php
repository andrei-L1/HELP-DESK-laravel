<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['ticket_id', 'user_id', 'is_internal', 'body'];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at'  => 'datetime',
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
