<?php

namespace App\Observers;

use App\Models\TicketMessage;
use App\Notifications\NewMessageNotification;

class TicketMessageObserver
{
    /**
     * Handle the TicketMessage "created" event.
     */
    public function created(TicketMessage $ticketMessage): void
    {
        // 1. Broadcast the message (Existing logic)
        $ticketMessage->load('user');
        broadcast(new \App\Events\TicketMessageSent($ticketMessage));

        // 2. Notification logic (Ignore internal notes for user)
        $ticket = $ticketMessage->ticket;
        if (!$ticket) return;

        // 2a. Update Ticket's updated_at and broadcast update to refresh lists
        $ticket->touch();
        broadcast(new \App\Events\TicketUpdated($ticket));

        // Determine recipients
        $recipients = collect();

        // 1. Notify the creator if they are not the sender and it's not internal
        if ($ticket->creator && $ticketMessage->user_id !== $ticket->created_by && !$ticketMessage->is_internal) {
            $recipients->push($ticket->creator);
        }

        // 2. Notify the assignee if they are not the sender
        if ($ticket->assignee && $ticketMessage->user_id !== $ticket->assigned_to) {
            $recipients->push($ticket->assignee);
        }

        // Send unique notifications
        $recipients->unique('id')->each(function ($user) use ($ticketMessage) {
            $user->notify(new NewMessageNotification($ticketMessage));
        });
    }

    /**

     * Handle the TicketMessage "updated" event.
     */
    public function updated(TicketMessage $ticketMessage): void
    {
        //
    }

    /**
     * Handle the TicketMessage "deleted" event.
     */
    public function deleted(TicketMessage $ticketMessage): void
    {
        //
    }

    /**
     * Handle the TicketMessage "restored" event.
     */
    public function restored(TicketMessage $ticketMessage): void
    {
        //
    }

    /**
     * Handle the TicketMessage "force deleted" event.
     */
    public function forceDeleted(TicketMessage $ticketMessage): void
    {
        //
    }
}
