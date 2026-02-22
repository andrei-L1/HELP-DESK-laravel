<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        // Manager's dashboard data payload

        // Basic Stats
        $stats = [
            'total_team_tickets' => Ticket::count(), // Adjust this to filter by department when you add Departments to Managers
            'pending_review' => Ticket::whereHas('status', function($q){ $q->where('name', 'Pending'); })->count(),
            'team_members' => User::whereHas('role', function($q) { $q->whereIn('name', ['agent', 'user']); })->count(),
            'avg_response_time' => '4h', // Placeholder - calculating actual average needs first_response_at diffing
        ];

        // Recent tickets (latest 5 to review or assign)
        $recentTickets = Ticket::with(['status', 'priority', 'assignee'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status->name ?? 'Unknown',
                    'priority' => $ticket->priority->name ?? 'Unknown',
                    'assigned_to' => $ticket->assignee ? trim("{$ticket->assignee->first_name} {$ticket->assignee->last_name}") : null,
                    'created_at' => $ticket->created_at->diffForHumans(),
                ];
            });

        // Tickets by status
        $statusCounts = Ticket::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->with('status')
            ->get();
            
        $ticketsByStatus = $statusCounts->map(function($record) {
            return [
                'name' => $record->status->name ?? 'Unknown',
                'count' => $record->count
            ];
        });

        // Team performance (Placeholder / simplified logic)
        $teamPerformance = User::whereHas('role', function($q) { $q->where('name', 'agent'); })
            ->withCount(['assignedTickets as assigned' => function($q) {
                $q->whereHas('status', function($s) { $s->where('name', '!=', 'Closed'); });
            }])
            ->get()
            ->map(function ($agent) {
                return [
                    'id' => $agent->id,
                    'name' => trim("{$agent->first_name} {$agent->last_name}"),
                    'email' => $agent->email,
                    'assigned' => $agent->assigned,
                    'resolved' => Ticket::where('assigned_to', $agent->id)
                                    ->whereHas('status', function($s) { $s->where('name', 'Resolved'); })
                                    ->count(),
                    'pending' => Ticket::where('assigned_to', $agent->id)
                                    ->whereHas('status', function($s) { $s->where('name', 'Pending'); })
                                    ->count(),
                    'avg_response' => '2h',
                ];
            });

        return Inertia::render('Manager/ManagerDashboard', [
            'stats' => $stats,
            'recent_tickets' => $recentTickets,
            'tickets_by_status' => $ticketsByStatus,
            'team_performance' => $teamPerformance,
        ]);
    }
}
