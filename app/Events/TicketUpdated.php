<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $action;
    public int $ticketId;
    public ?int $creatorId;

    /**
     * Create a new event instance.
     *
     * @param string   $action    The action that occurred: 'created', 'updated', 'replied', etc.
     * @param int      $ticketId  The ticket that was affected
     * @param int|null $creatorId The user who originally created the ticket
     */
    public function __construct(string $action, int $ticketId, ?int $creatorId = null)
    {
        $this->action = $action;
        $this->ticketId = $ticketId;
        $this->creatorId = $creatorId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('staff.tickets'),
        ];

        if ($this->creatorId) {
            $channels[] = new PrivateChannel("user.{$this->creatorId}.tickets");
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'ticket.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'ticket_id' => $this->ticketId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
