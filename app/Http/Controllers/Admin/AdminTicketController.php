<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
    public function assigned(Request $request)
    {
        $assignedTo = Auth::id();
        return $this->getTickets($request, null, 'assigned', 'Admin/Tickets/Assigned', $assignedTo);
    }
    /**
     * Show the form for creating a new ticket (full page).
     */
    public function create()
    {
        return Inertia::render('Admin/Tickets/Create');
    }

    /**
     * Show a single ticket (detail page) with assign option.
     */
    public function show(int $id)
    {
        $ticket = DB::table('tickets')
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->leftJoin('users as created_by_user', 'tickets.created_by', '=', 'created_by_user.id')
            ->leftJoin('users as assigned_to_user', 'tickets.assigned_to', '=', 'assigned_to_user.id')
            ->whereNull('tickets.deleted_at')
            ->where('tickets.id', $id)
            ->select(
                'tickets.id',
                'tickets.ticket_number',
                'tickets.subject',
                'tickets.description',
                'tickets.assigned_to as assigned_to_id',
                'ticket_statuses.name as status',
                'ticket_priorities.name as priority',
                DB::raw("CONCAT(COALESCE(created_by_user.first_name, ''), ' ', COALESCE(created_by_user.last_name, '')) as created_by"),
                DB::raw("CONCAT(COALESCE(assigned_to_user.first_name, ''), ' ', COALESCE(assigned_to_user.last_name, '')) as assigned_to_name"),
                'tickets.created_at',
                'tickets.updated_at'
            )
            ->first();

        if (!$ticket) {
            abort(404);
        }

        $users = $this->getAssignableUsers();

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => [
                'id'              => $ticket->id,
                'ticket_number'   => $ticket->ticket_number,
                'subject'         => $ticket->subject,
                'description'     => $ticket->description,
                'status'          => $ticket->status ?? 'Unknown',
                'priority'        => $ticket->priority ?? 'Unknown',
                'created_by'      => trim($ticket->created_by) ?: 'Unknown',
                'assigned_to_id'  => $ticket->assigned_to_id,
                'assigned_to_name'=> trim($ticket->assigned_to_name) ?: null,
                'created_at'      => $ticket->created_at ? \Carbon\Carbon::parse($ticket->created_at)->toDateTimeString() : null,
                'updated_at'      => $ticket->updated_at ? \Carbon\Carbon::parse($ticket->updated_at)->toDateTimeString() : null,
            ],
            'users' => $users,
        ]);
    }

    /**
     * Update ticket (e.g. assign/reassign). Only managers and agents can be assigned.
     */
    public function update(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $assignableIds = $this->getAssignableUserIds();

        $validated = $request->validate([
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::in($assignableIds),
            ],
        ]);

        $ticket->assigned_to = $validated['assigned_to'] ?? null;
        $ticket->save();

        return redirect()->route('admin.tickets.show', $ticket->id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'     => 'required|string|max:200',
            'description' => 'required|string',
            'priority'    => 'required|string|in:low,medium,high',
        ]);

        $priorityName = ucfirst(strtolower($validated['priority']));
        $priorityId   = DB::table('ticket_priorities')->where('name', $priorityName)->value('id');
        $statusId     = DB::table('ticket_statuses')->where('name', 'Open')->value('id');

        if (!$priorityId || !$statusId) {
            return back()->withErrors(['priority' => 'Invalid priority or status configuration. Run seeders.']);
        }

        $year    = date('Y');
        $count   = Ticket::whereYear('created_at', $year)->count();
        $ticketNumber = 'TICKET-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $assignedTo = $this->pickAutoAssignUserId();

        Ticket::create([
            'ticket_number' => $ticketNumber,
            'subject'       => $validated['subject'],
            'description'   => $validated['description'],
            'status_id'     => $statusId,
            'priority_id'   => $priorityId,
            'created_by'    => Auth::id(),
            'assigned_to'   => $assignedTo,
        ]);

        return redirect()->route('admin.tickets.index');
    }

    /**
     * Users who can be assigned tickets: managers and agents only.
     */
    private function getAssignableUsers(): array
    {
        return DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', ['manager', 'agent'])
            ->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->get(['users.id', 'users.first_name', 'users.last_name'])
            ->map(fn ($u) => [
                'id'   => $u->id,
                'name' => trim("{$u->first_name} {$u->last_name}") ?: "User #{$u->id}",
            ])
            ->values()
            ->all();
    }

    private function getAssignableUserIds(): array
    {
        return DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', ['manager', 'agent'])
            ->pluck('users.id')
            ->all();
    }

    /**
     * Pick a user for auto-assign: the assignable user with the fewest open tickets (round-robin style).
     */
    private function pickAutoAssignUserId(): ?int
    {
        $openStatusId = DB::table('ticket_statuses')->where('name', 'Open')->value('id');
        if (!$openStatusId) {
            return null;
        }

        $assignableIds = $this->getAssignableUserIds();
        if (empty($assignableIds)) {
            return null;
        }

        $counts = Ticket::query()
            ->where('status_id', $openStatusId)
            ->whereIn('assigned_to', $assignableIds)
            ->selectRaw('assigned_to as user_id, count(*) as open_count')
            ->groupBy('assigned_to')
            ->pluck('open_count', 'user_id')
            ->all();

        $minCount = null;
        $pickId   = null;
        foreach ($assignableIds as $id) {
            $c = $counts[$id] ?? 0;
            if ($minCount === null || $c < $minCount) {
                $minCount = $c;
                $pickId   = $id;
            }
        }
        return $pickId ?? $assignableIds[0];
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

        // ────────────────────────────────────────────────
        // Apply status from URL/query (?status=...) if present
        // This makes the Vue dropdown work
        if ($request->filled('status')) {
            $query->where('ticket_statuses.name', $request->status);
        }
        // Keep the view-specific hard filter (open(), assigned(), etc.) if you want
        else if ($statusFilter !== null) {
            $query->where('ticket_statuses.name', $statusFilter);
        }
        // ────────────────────────────────────────────────

        if ($view === 'assigned' && $assignedTo) {
            $query->where('tickets.assigned_to', $assignedTo);
        }

        // Search filter
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