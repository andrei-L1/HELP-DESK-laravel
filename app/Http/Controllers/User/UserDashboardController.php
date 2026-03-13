<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ticket;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Get recent tickets for the user
        $recentTickets = Ticket::with(['status', 'priority'])
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status->name ?? 'Unknown',
                    'priority' => $ticket->priority->name ?? 'Unknown',
                    'created_at' => $ticket->created_at->diffForHumans(),
                ];
            });

        // Get basic stats
        $stats = [
            'total_tickets' => Ticket::where('created_by', $user->id)->count(),
            'open_tickets' => Ticket::where('created_by', $user->id)
                ->whereHas('status', function($q){ 
                    $q->where('name', '!=', 'Closed')->where('name', '!=', 'Resolved'); 
                })->count(),
            'closed_tickets' => Ticket::where('created_by', $user->id)
                ->whereHas('status', function($q){ 
                    $q->whereIn('name', ['Closed', 'Resolved']); 
                })->count(),
        ];

        return Inertia::render('User/Dashboard', [
            'recent_tickets' => $recentTickets,
            'stats' => $stats,
        ]);
    }
}
