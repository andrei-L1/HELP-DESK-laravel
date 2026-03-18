<?php

namespace App\Events;

use App\Models\TicketMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageData;
    public $ticketId;

    /**
     * Create a new event instance.
     */
    public function __construct(TicketMessage $message)
    {
        $this->ticketId = $message->ticket_id;
        
        // Prepare the data for the frontend
        $this->messageData = [
            'id'          => $message->id,
            'body'        => $message->body,
            'user_id'     => $message->user_id,
            'is_internal' => (bool) $message->is_internal,
            'author'      => $message->user ? trim($message->user->first_name . ' ' . $message->user->last_name) : 'User #' . $message->user_id,
            'user'        => [
                'id'         => $message->user_id,
                'first_name' => $message->user->first_name ?? 'User',
                'last_name'  => $message->user->last_name ?? '',
                'avatar_url' => $message->user->avatar_url ?? null,
            ],
            'created_at'  => $message->created_at?->toDateTimeString(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->messageData['is_internal']) {
            return [
                new PrivateChannel('ticket.' . $this->ticketId . '.internal'),
            ];
        }

        return [
            new PrivateChannel('ticket.' . $this->ticketId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TicketMessageSent';
    }
}
