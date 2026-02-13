<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
                    'ticket_priorities.name as priority',
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
                        'priority' => $ticket->priority ?? 'Unknown',
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

        return Inertia::render('Admin/AdminDashboard', [
            'stats' => $stats,
            'recent_tickets' => $recent_tickets,
            'tickets_by_status' => $tickets_by_status,
        ]);
    }
}
