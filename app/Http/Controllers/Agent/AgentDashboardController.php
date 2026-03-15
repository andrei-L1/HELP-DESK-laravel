<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AgentDashboardController extends Controller
{
    protected function agentTicketsQuery($user)
    {
        return Ticket::where('assigned_to', $user->id);
    }

    public function index()
    {
        $user = Auth::user();

        // Basic personal stats
        $stats = [
            'total_my_tickets'    => $this->agentTicketsQuery($user)->count(),
            'my_open_tickets'     => $this->agentTicketsQuery($user)
                                        ->whereHas('status', fn($q) => $q->where('name', 'Open'))
                                        ->count(),
            'my_pending_tickets'  => $this->agentTicketsQuery($user)
                                        ->whereHas('status', fn($q) => $q->where('name', 'Pending'))
                                        ->count(),
            'my_resolved_tickets' => $this->agentTicketsQuery($user)
                                        ->whereHas('status', fn($q) => $q->where('name', 'Resolved'))
                                        ->count(),
            'avg_response_time'   => '2h 14m', // ← placeholder – real calc requires first_response_at
        ];

        // Recent assigned tickets (latest 8)
        $recentTickets = $this->agentTicketsQuery($user)->with(['status', 'priority'])
            ->orderByDesc('updated_at') // or created_at – depending on preference
            ->take(8)
            ->get()
            ->map(fn($ticket) => [
                'id'            => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject'       => $ticket->subject,
                'status'        => $ticket->status->name ?? 'Unknown',
                'priority'      => $ticket->priority->name ?? 'Unknown',
                'created_at'    => $ticket->created_at->diffForHumans(),
            ]);

        return Inertia::render('Agent/Dashboard', [
            'stats'         => $stats,
            'recent_tickets' => $recentTickets,
        ]);
    }
}