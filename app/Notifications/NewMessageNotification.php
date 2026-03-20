<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(\App\Models\TicketMessage $message)
    {
        $this->message = $message;
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
        return 'ticket.new_message';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->message->ticket;
        $url = $this->getTicketUrl($notifiable, $ticket);

        return (new MailMessage)
            ->subject('New Message on Ticket: ' . $ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You have received a new message on ticket **' . $ticket->ticket_number . '**.')
            ->line('**From:** ' . ($this->message->user?->display_name ?? 'System'))
            ->line('**Message Snippet:** ' . \Illuminate\Support\Str::limit($this->message->message, 100))
            ->action('Reply to Ticket', $url)
            ->line('Thank you for using our support portal!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $ticket = $this->message->ticket;

        return [
            'ticket_id'      => $ticket->id,
            'ticket_number'  => $ticket->ticket_number,
            'message_id'     => $this->message->id,
            'subject'        => 'New Message: ' . $ticket->ticket_number,
            'message'        => 'New message from ' . ($this->message->user?->display_name ?? 'System'),
            'type'           => 'new_message',
            'url'            => $this->getTicketUrl($notifiable, $ticket),
        ];
    }

    /**
     * Build the correct URL based on user role.
     */
    protected function getTicketUrl(object $notifiable, $ticket): string
    {
        if ($notifiable->isAdmin()) {
            return route('admin.tickets.show', $ticket->id);
        } elseif ($notifiable->isManager()) {
            return route('manager.tickets.show', $ticket->id);
        } elseif ($notifiable->isAgent()) {
            return route('agent.tickets.show', $ticket->id);
        }

        return route('user.tickets.show', $ticket->id);
    }
}

