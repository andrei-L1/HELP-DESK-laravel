<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminTicketController extends Controller
{
    /**
     * Display a paginated list of ALL tickets for admins.
     */
    public function index(Request $request)
    {
        return $this->getTickets($request, null, 'all', 'Admin/Tickets/All'); 
    }

    public function open(Request $request)
    {
        return $this->getTickets($request, 'Open', 'open', 'Admin/Tickets/Open');
    }


    /**
     * Shared logic for fetching and formatting tickets.
     *
     * @param Request $request
     * @param string|null $statusFilter  Specific status to filter by (null = all)
     * @param string $view               Identifier for the frontend ('all', 'open', etc.)
     */
    private function getTickets(Request $request, ?string $statusFilter, string $view, string $component, ?int $assignedTo = null)
    {
        $query = DB::table('tickets')
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
            ->orderByDesc('tickets.created_at');

        // Status filter
        if ($statusFilter !== null) {
            $query->where('ticket_statuses.name', $statusFilter);
        }

        if ($view === 'assigned' && $assignedTo) {
            $query->where('tickets.assigned_to', $assignedTo);
        }

        // Search filter (applies to both views)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tickets.ticket_number', 'like', "%{$search}%")
                  ->orWhere('tickets.subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Transform collection (same as before)
        $tickets->getCollection()->transform(function ($ticket) {
            return [
                'id'           => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject'      => $ticket->subject,
                'status'       => $ticket->status ?? 'Unknown',
                'priority'     => $ticket->priority ?? 'Unknown',
                'created_by'   => trim($ticket->created_by) ?: 'Unknown',
                'assigned_to'  => trim($ticket->assigned_to) ?: 'Unassigned',
                'created_at'   => \Carbon\Carbon::parse($ticket->created_at)->toDateTimeString(),
            ];
        });

        // Statuses for dropdown (you can still show all statuses, or filter them if desired)
        $statuses = DB::table('ticket_statuses')
            ->select('name')
            ->orderBy('name')
            ->pluck('name');

        return Inertia::render($component, [   // ← dynamic component name
                'tickets'  => $tickets,
                'filters'  => $request->only(['search', 'status']),
                'statuses' => $statuses,
                'view'     => $view,               // still useful for highlighting sidebar etc.
            ]);
    }
}