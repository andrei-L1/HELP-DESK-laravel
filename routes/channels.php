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

Broadcast::channel('ticket.{ticketId}.presence', function ($user, $ticketId) {
    if (!$user->isAdmin() && !$user->isManager() && !$user->isAgent()) {
        return false;
    }

    $ticket = \App\Models\Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->first_name . ' ' . $user->last_name,
        'role' => $user->role->name ?? 'Staff',
        'avatar_initials' => strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1))
    ];
});

Broadcast::channel('staff.tickets', function ($user) {
    return $user->isAdmin() || $user->isManager() || $user->isAgent();
});

Broadcast::channel('user.{userId}.tickets', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
