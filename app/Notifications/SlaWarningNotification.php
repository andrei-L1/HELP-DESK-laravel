<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaWarningNotification extends Notification implements ShouldQueue
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
        $channels = [];
        
        if ($notifiable->wantsNotification('sla_warning', 'mail')) {
            $channels[] = 'mail';
        }
        
        if ($notifiable->wantsNotification('sla_warning', 'database')) {
            $channels[] = 'database';
        }

        if ($notifiable->wantsNotification('sla_warning', 'broadcast')) {
            $channels[] = 'broadcast';
        }
        
        return $channels;
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): \Illuminate\Notifications\Messages\BroadcastMessage
    {
        return new \Illuminate\Notifications\Messages\BroadcastMessage($this->toArray($notifiable));
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->getTicketUrl($notifiable);
        $dueTime = $this->ticket->due_at ? $this->ticket->due_at->format('M d, Y H:i') : 'Unknown';

        return (new MailMessage)
            ->subject('⚠️ SLA Warning: Ticket #' . $this->ticket->ticket_number)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('The following ticket is approaching its SLA resolution limit.')
            ->line('**Ticket #:** ' . $this->ticket->ticket_number)
            ->line('**Subject:** ' . $this->ticket->subject)
            ->line('**Due At:** ' . $dueTime)
            ->action('View Ticket', $url)
            ->line('Please address this ticket as soon as possible to avoid a breach.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id'      => $this->ticket->id,
            'ticket_number'  => $this->ticket->ticket_number,
            'subject'        => $this->ticket->subject,
            'due_at'         => $this->ticket->due_at ? $this->ticket->due_at->toDateTimeString() : null,
            'message'        => 'SLA Warning: Ticket #' . $this->ticket->ticket_number . ' is approaching its limit.',
            'type'           => 'warning',
            'url'            => $this->getTicketUrl($notifiable),
        ];
    }

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
