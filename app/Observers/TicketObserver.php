<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketStatusChangedNotification;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        // 1. Notify the Creator (User)
        if ($ticket->creator) {
            $ticket->creator->notify(new TicketCreatedNotification($ticket));
        }

        // 2. Notify the Assignee (Agent/Manager/Admin) if already assigned
        if ($ticket->assignee && $ticket->assigned_to !== $ticket->created_by) {
            $ticket->assignee->notify(new TicketAssignedNotification($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // 1. Check if assigned_to changed
        if ($ticket->isDirty('assigned_to') && $ticket->assigned_to) {
            if ($ticket->assignee && $ticket->assigned_to !== $ticket->created_by) {
                $ticket->assignee->notify(new TicketAssignedNotification($ticket));
            }
        }

        // 2. Check if status changed
        if ($ticket->isDirty('status_id')) {
            if ($ticket->creator) {
                // Load status relation if not loaded
                $ticket->loadMissing('status');
                $ticket->creator->notify(new TicketStatusChangedNotification($ticket));
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
