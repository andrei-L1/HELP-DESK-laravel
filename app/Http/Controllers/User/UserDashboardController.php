<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Get recent tickets for the user with full status and priority data including colors
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
                    'status' => [
                        'name' => $ticket->status->name ?? 'Unknown',
                        'title' => $ticket->status->title ?? $ticket->status->name ?? 'Unknown',
                        'color_hex' => $ticket->status->color_hex ?? '#6b7280', // Default gray if no color
                        'is_closed' => $ticket->status->is_closed ?? false,
                        'is_resolved' => $ticket->status->is_resolved ?? false,
                    ],
                    'priority' => [
                        'name' => $ticket->priority->name ?? 'Unknown',
                        'level' => $ticket->priority->level ?? 0,
                        'color_hex' => $ticket->priority->color_hex ?? '#6b7280', // Default gray if no color
                    ],
                    'created_at' => $ticket->created_at->diffForHumans(),
                ];
            });

        // Get basic stats with proper status filtering
        $stats = [
            'total_tickets' => Ticket::where('created_by', $user->id)->count(),
            'open_tickets' => Ticket::where('created_by', $user->id)
                ->whereHas('status', function($q) { 
                    $q->where('is_closed', false)
                      ->where('is_resolved', false); 
                })->count(),
            'closed_tickets' => Ticket::where('created_by', $user->id)
                ->whereHas('status', function($q) { 
                    $q->where('is_closed', true)
                      ->orWhere('is_resolved', true); 
                })->count(),
        ];

        return Inertia::render('User/Dashboard', [
            'recent_tickets' => $recentTickets,
            'stats' => $stats,
        ]);
    }
}