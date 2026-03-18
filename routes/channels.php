<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int)$user->id === (int)$id;
});

Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = \App\Models\Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }

    return $user->id === $ticket->created_by ||
           $user->id === $ticket->assigned_to ||
           $user->isAdmin() ||
           $user->isManager();
});

Broadcast::channel('ticket.{ticketId}.internal', function ($user, $ticketId) {
    if (!$user->isAdmin() && !$user->isManager() && !$user->isAgent()) {
        return false;
    }

    $ticket = \App\Models\Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }

    return $user->id === $ticket->assigned_to ||
           $user->isAdmin() ||
           $user->isManager();
});
