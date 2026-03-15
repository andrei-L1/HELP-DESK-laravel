<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        // Manager's dashboard data payload
        $userDepartments = auth()->user()->departments()->pluck('departments.id');

        // Basic Stats
        $avgResponseMinutes = Ticket::whereIn('department_id', $userDepartments)
            ->whereNotNull('first_response_at')
            ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, first_response_at)'));

        $stats = [
            'total_team_tickets' => Ticket::whereIn('department_id', $userDepartments)->count(),
            'pending_review' => Ticket::whereIn('department_id', $userDepartments)->whereHas('status', function ($q) {
            $q->where('name', 'Pending'); })->count(),
            'team_members' => User::whereHas('role', function ($q) {
            $q->whereIn('name', ['agent', 'user']); })
            ->whereHas('departments', function ($q) use ($userDepartments) {
            $q->whereIn('departments.id', $userDepartments);
        })->count(),
            'avg_response_time' => $this->formatMinutes($avgResponseMinutes),
        ];

        // Recent tickets (latest 5 to review or assign)
        $recentTickets = Ticket::with(['status', 'priority', 'assignee'])
            ->whereIn('department_id', $userDepartments)
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
            ->whereIn('department_id', $userDepartments)
            ->groupBy('status_id')
            ->with('status')
            ->get();

        $ticketsByStatus = $statusCounts->map(function ($record) {
            return [
            'name' => $record->status->name ?? 'Unknown',
            'count' => $record->count
            ];
        });

        // Team performance (Placeholder / simplified logic)
        $teamPerformance = User::whereHas('role', function ($q) {
            $q->where('name', 'agent'); })
            ->whereHas('departments', function ($q) use ($userDepartments) {
            $q->whereIn('departments.id', $userDepartments);
        })
            ->withCount(['assignedTickets as assigned' => function ($q) use ($userDepartments) {
            $q->whereIn('department_id', $userDepartments)
                ->whereHas('status', function ($s) {
                $s->where('name', '!=', 'Closed'); }
            );
        }])
            ->get()
            ->map(function ($agent) use ($userDepartments) {
            $avgMins = Ticket::where('assigned_to', $agent->id)
                ->whereIn('department_id', $userDepartments)
                ->whereNotNull('first_response_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, first_response_at)'));
            return [
            'id' => $agent->id,
            'name' => trim("{$agent->first_name} {$agent->last_name}"),
            'email' => $agent->email,
            'assigned' => $agent->assigned,
            'resolved' => Ticket::where('assigned_to', $agent->id)
            ->whereIn('department_id', $userDepartments)
            ->whereHas('status', function ($s) {
                    $s->where('name', 'Resolved'); }
                )
                ->count(),
                'pending' => Ticket::where('assigned_to', $agent->id)
                ->whereIn('department_id', $userDepartments)
                ->whereHas('status', function ($s) {
                    $s->where('name', 'Pending'); }
                )
                ->count(),
                'avg_response' => $this->formatMinutes($avgMins),
                ];
            });

        return Inertia::render('Manager/Dashboard', [
            'stats' => $stats,
            'recent_tickets' => $recentTickets,
            'tickets_by_status' => $ticketsByStatus,
            'team_performance' => $teamPerformance,
        ]);
    }

    /**
     * Format a number of minutes into a human-readable string like "2h 15m" or "N/A".
     */
    private function formatMinutes(?float $minutes): string
    {
        if ($minutes === null || $minutes <= 0) {
            return 'N/A';
        }
        $h = (int) floor($minutes / 60);
        $m = (int) round($minutes % 60);
        if ($h === 0) {
            return "{$m}m";
        }
        return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
    }
}
