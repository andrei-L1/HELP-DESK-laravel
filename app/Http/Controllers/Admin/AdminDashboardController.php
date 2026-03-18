<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get statistics using DB queries (models will be created later)
        // Use try-catch to handle cases where tables don't exist yet
        try {
            $stats = [
                'total_tickets' => DB::table('tickets')->count(),
                'open_tickets' => DB::table('tickets')
                    ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                    ->where('ticket_statuses.name', '!=', 'closed')
                    ->whereNull('tickets.deleted_at')
                    ->count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_departments' => DB::table('departments')
                    ->whereNull('deleted_at')
                    ->count(),
            ];
        } catch (\Exception $e) {
            // If tables don't exist yet, return zeros
            $stats = [
                'total_tickets' => 0,
                'open_tickets' => 0,
                'active_users' => User::where('is_active', true)->count(),
                'total_departments' => 0,
            ];
        }

        // Get recent tickets (last 10) with related data
        try {
            $recent_tickets = DB::table('tickets')
                ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
                ->leftJoin('users as created_by_user', 'tickets.created_by', '=', 'created_by_user.id')
                ->leftJoin('users as assigned_to_user', 'tickets.assigned_to', '=', 'assigned_to_user.id')
                ->whereNull('tickets.deleted_at')
                ->select(
                    'tickets.id',
                    'tickets.ticket_number',
                    'tickets.subject',
                    'ticket_statuses.name as status',
                    'ticket_statuses.color_hex as status_color',
                    'ticket_priorities.name as priority',
                    'ticket_priorities.color_hex as priority_color',
                    DB::raw("CONCAT(COALESCE(created_by_user.first_name, ''), ' ', COALESCE(created_by_user.last_name, '')) as created_by"),
                    DB::raw("CONCAT(COALESCE(assigned_to_user.first_name, ''), ' ', COALESCE(assigned_to_user.last_name, '')) as assigned_to"),
                    'tickets.created_at'
                )
                ->orderBy('tickets.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'subject' => $ticket->subject,
                        'status' => $ticket->status ?? 'Unknown',
                        'status_color' => $ticket->status_color ?? '#64748b',
                        'priority' => $ticket->priority ?? 'Unknown',
                        'priority_color' => $ticket->priority_color ?? '#64748b',
                        'created_by' => trim($ticket->created_by) ?: 'Unknown',
                        'assigned_to' => trim($ticket->assigned_to) ?: 'Unassigned',
                        'created_at' => \Carbon\Carbon::parse($ticket->created_at)->diffForHumans(),
                    ];
                });
        } catch (\Exception $e) {
            $recent_tickets = collect([]);
        }

        // Get tickets by status (for potential chart/visualization)
        try {
            $tickets_by_status = DB::table('tickets')
                ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                ->whereNull('tickets.deleted_at')
                ->select('ticket_statuses.name', DB::raw('count(*) as count'))
                ->groupBy('ticket_statuses.name')
                ->get();
        } catch (\Exception $e) {
            $tickets_by_status = collect([]);
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_tickets' => $recent_tickets,
            'tickets_by_status' => $tickets_by_status,
        ]);
    }

    public function analytics(Request $request)
    {
        // 1. Setup Time Periods based on Filter
        $period = (int) $request->get('period', 7);
        $periodValue = in_array($period, [7, 30, 90]) ? $period : 7;
        
        $now = now();
        $startDate = $now->copy()->subDays($periodValue);
        $prevStartDate = $now->copy()->subDays($periodValue * 2);

        // Resolution Time (ART)
        $resRow = DB::table('tickets')
            ->whereNotNull('resolved_at')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)) as avg_time'))
            ->first();
        $avgResolutionSeconds = $resRow ? $resRow->avg_time : 0;
        
        // First Response Time (FRT)
        $respRow = DB::table('tickets')
            ->whereNotNull('first_response_at')
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, first_response_at)) as avg_time'))
            ->first();
        $avgResponseSeconds = $respRow ? $respRow->avg_time : 0;

        $overview_stats = [
            'resolution_time' => $this->formatSeconds($avgResolutionSeconds),
            'first_response' => $this->formatSeconds($avgResponseSeconds),
            'csat' => '4.8/5', // Mocked as not collected yet
            'satisfaction_trend' => '+1.2%' // Mocked
        ];

        // 2. Volume & Growth
        $thisPeriodCount = DB::table('tickets')
            ->where('created_at', '>=', $startDate)
            ->whereNull('deleted_at')
            ->count();
        
        $prevPeriodCount = DB::table('tickets')
            ->where('created_at', '>=', $prevStartDate)
            ->where('created_at', '<', $startDate)
            ->whereNull('deleted_at')
            ->count();

        $growth = $prevPeriodCount > 0 
            ? round((($thisPeriodCount - $prevPeriodCount) / $prevPeriodCount) * 100, 1) 
            : 100;

        // 3. Dynamic Volume Data (Daily)
        $limit = $periodValue; // Limit to number of days in period
        $dailyVolume = DB::table('tickets')
            ->where('created_at', '>=', $startDate)
            ->whereNull('deleted_at')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => Carbon::parse($item->date)->format('D'),
                    'count' => $item->count
                ];
            });

        // 4. Agent Performance within period
        $agentPerformance = User::whereHas('role', function($q) {
                $q->whereIn('name', ['admin', 'manager', 'agent']);
            })
            ->withCount(['assignedTickets as resolved_count' => function($q) use ($startDate) {
                $q->whereNotNull('resolved_at')->where('resolved_at', '>=', $startDate);
            }])
            ->get()
            ->map(function($user) use ($startDate) {
                $agentResRow = DB::table('tickets')
                    ->where('assigned_to', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->whereNotNull('first_response_at')
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, first_response_at)) as avg_time'))
                    ->first();
                $avgRes = $agentResRow ? $agentResRow->avg_time : 0;

                return [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'avatar' => substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1),
                    'resolved' => $user->resolved_count,
                    'avg_time' => $this->formatSeconds($avgRes),
                    'rating' => number_format(4.5 + (rand(0, 5) / 10), 1) // Mocked
                ];
            })
            ->sortByDesc('resolved')
            ->values()
            ->take(5);

        return Inertia::render('Admin/Analytics', [
            'overview_stats' => $overview_stats,
            'volume_data' => $dailyVolume,
            'weekly_total' => $thisPeriodCount,
            'weekly_growth' => $growth > 0 ? "+$growth%" : "$growth%",
            'agent_performance' => $agentPerformance,
            'current_period' => $periodValue
        ]);
    }

    private function formatSeconds($seconds)
    {
        if ($seconds < 60) return round($seconds) . 's';
        if ($seconds < 3600) return round($seconds / 60, 1) . 'm';
        return round($seconds / 3600, 1) . 'h';
    }
}
