<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ticket;

    /**
     * Create a new event instance.
     */
    public function __construct(Ticket $ticket)
    {
        // Load relationships needed for the frontend display
        $this->ticket = $ticket->load(['status', 'priority', 'creator', 'department', 'assignee']);
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'ticket' => (new \App\Http\Resources\TicketResource($this->ticket))->resolve()
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('staff.tickets')
        ];

        if ($this->ticket->created_by) {
            $channels[] = new PrivateChannel('user.' . $this->ticket->created_by . '.tickets');
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TicketCreated';
    }
}
