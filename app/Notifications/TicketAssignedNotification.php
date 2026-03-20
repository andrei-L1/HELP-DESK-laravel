<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(\App\Models\Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the type of the notification being broadcast.
     */
    public function broadcastType(): string
    {
        return 'ticket.assigned';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->getTicketUrl($notifiable);

        return (new MailMessage)
            ->subject('New Ticket Assigned: ' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You have been assigned a new support ticket.')
            ->line('**Ticket #:** ' . $this->ticket->ticket_number)
            ->line('**Subject:** ' . $this->ticket->subject)
            ->action('View Ticket Details', $url)
            ->line('Please look into this at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject'       => $this->ticket->subject,
            'message'       => 'You have been assigned ticket: ' . $this->ticket->ticket_number,
            'type'          => 'ticket_assigned',
            'url'           => $this->getTicketUrl($notifiable),
        ];
    }

    /**
     * Build the correct URL based on user role.
     */
    protected function getTicketUrl(object $notifiable): string
    {
        if ($notifiable->isAdmin()) {
            return route('admin.tickets.show', $this->ticket->id);
        } elseif ($notifiable->isManager()) {
            return route('manager.tickets.show', $this->ticket->id);
        } elseif ($notifiable->isAgent()) {
            return route('agent.tickets.show', $this->ticket->id);
        }

        return route('user.tickets.show', $this->ticket->id);
    }
}

