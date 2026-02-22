<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Admin\AdminTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerTicketController extends AdminTicketController
{
    /**
     * Display a paginated list of ALL tickets for managers.
     */
    public function all(Request $request)
    {
        return $this->getTickets($request, null, 'all', 'Manager/Tickets/All');
    }
    public function index(Request $request)
    {
        return $this->getTickets($request, null, 'index', 'Manager/Tickets/Index'); 
    }

    /**
     * Override the base getTickets method to filter tickets by the manager's departments.
     */
    protected function getTickets(Request $request, ?string $statusFilter, string $view, string $component, ?int $assignedTo = null)
    {
        // Get the manager's departments
        $userDepartments = Auth::user()->departments()->pluck('departments.id');

        // Capture original request parameters to merge with our department filter
        // Actually, overriding getTickets fully isn't optimal if we just want to inject a base query. 
        // But since AdminTicketController builds the query entirely inside getTickets, we have to override it.
        // Or better yet, we can modify AdminTicketController to accept a base query.
        // Let's modify AdminTicketController to have a protected function getBaseTicketQuery() which we can override.
        // For now, let's just do it directly if we can... Let's use the alternative: override the method and merge $userDepartments.
        
        $query = \App\Models\Ticket::with(['status', 'priority', 'creator', 'assignee'])
            ->whereIn('department_id', $userDepartments)
            ->orderByDesc('created_at');

        // Apply status filter from URL/query
        if ($request->filled('status')) {
            $query->whereHas('status', fn($q) => $q->where('name', $request->status));
        }
        // Apply view-specific hard filter
        else if ($statusFilter !== null) {
            $query->whereHas('status', fn($q) => $q->where('name', $statusFilter));
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->whereHas('priority', fn($q) => $q->where('name', $request->priority));
        }

        // Apply department filter from request (must be within user's departments)
        if ($request->filled('department') && in_array($request->department, $userDepartments->toArray())) {
            $query->where('department_id', $request->department);
        }

        // Apply assigned filter for assigned view
        if ($view === 'assigned' && $assignedTo) {
            $query->where('assigned_to', $assignedTo);
        }

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Transform collection for the frontend
        $tickets->getCollection()->transform(function ($ticket) {
            return [
                'id'            => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject'       => $ticket->subject,
                'status'        => $ticket->status->name ?? 'Unknown',
                'priority'      => $ticket->priority->name ?? 'Unknown',
                'created_by'    => $ticket->creator ? trim($ticket->creator->first_name . ' ' . $ticket->creator->last_name) : 'Unknown',
                'assigned_to'   => $ticket->assignee ? trim($ticket->assignee->first_name . ' ' . $ticket->assignee->last_name) : 'Unassigned',
                'created_at'    => $ticket->created_at->toDateTimeString(),
            ];
        });

        // Optimize stats query (filtered by user's departments)
        $statusCounts = \App\Models\Ticket::whereIn('department_id', $userDepartments)->select('status_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))->groupBy('status_id')->pluck('count', 'status_id');
        $priorityCounts = \App\Models\Ticket::whereIn('department_id', $userDepartments)->select('priority_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))->groupBy('priority_id')->pluck('count', 'priority_id');

        $statusMap = \Illuminate\Support\Facades\DB::table('ticket_statuses')->pluck('id', 'name');
        $priorityMap = \Illuminate\Support\Facades\DB::table('ticket_priorities')->pluck('id', 'name');

        $stats = [
            'total'     => \App\Models\Ticket::whereIn('department_id', $userDepartments)->count(),
            'open'      => $statusCounts[$statusMap['Open'] ?? 0] ?? 0,
            'pending'   => $statusCounts[$statusMap['Pending'] ?? 0] ?? 0,
            'resolved'  => $statusCounts[$statusMap['Resolved'] ?? 0] ?? 0,
            'closed'    => $statusCounts[$statusMap['Closed'] ?? 0] ?? 0,
            'urgent'    => $priorityCounts[$priorityMap['Urgent'] ?? 0] ?? 0,
            'high'      => $priorityCounts[$priorityMap['High'] ?? 0] ?? 0,
        ];

        // Retrieve dropdown filter maps
        $statuses = \Illuminate\Support\Facades\DB::table('ticket_statuses')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->pluck('name');
        $priorities = \Illuminate\Support\Facades\DB::table('ticket_priorities')->orderBy('sort_order')->orderBy('name')->pluck('name');
        $categories = \Illuminate\Support\Facades\DB::table('ticket_categories')->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'title']);
        $departments = \Illuminate\Support\Facades\DB::table('departments')->whereIn('id', $userDepartments)->whereNull('deleted_at')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'short_code']);

        return \Inertia\Inertia::render($component, [
            'tickets'     => $tickets,
            'filters'     => $request->only(['search', 'status', 'priority', 'department']),
            'statuses'    => $statuses,
            'priorities'  => $priorities,
            'categories'  => $categories,
            'departments' => $departments,
            'stats'       => $stats,
            'view'        => $view,
        ]);
    }

    public function open(Request $request)
    {
        return $this->getTickets($request, 'Open', 'open', 'Manager/Tickets/Open');
    }
    public function assigned(Request $request)
    {
        $assignedTo = Auth::id();
        return $this->getTickets($request, null, 'assigned', 'Manager/Tickets/Assigned', $assignedTo);
    }

    /**
     * Check if the manager is authorized to access the given ticket ID.
     */
    protected function authorizeTicketAccess(int $id)
    {
        $ticket = \App\Models\Ticket::findOrFail($id);
        $userDepartments = Auth::user()->departments()->pluck('departments.id');
        if (!in_array($ticket->department_id, $userDepartments->toArray())) {
            abort(403, 'Unauthorized access to this ticket.');
        }
    }

    /**
     * Show a single ticket (detail page) with assign option.
     */
    public function show(int $id)
    {
        $this->authorizeTicketAccess($id);

        $response = parent::show($id);
        if (is_a($response, \Inertia\Response::class)) {
            $reflection = new \ReflectionClass($response);
            $property = $reflection->getProperty('component');
            $property->setAccessible(true);
            $property->setValue($response, 'Manager/Tickets/Show');

            $propsProp = $reflection->getProperty('props');
            $propsProp->setAccessible(true);
            $props = $propsProp->getValue($response);

            $userDepartments = Auth::user()->departments()->pluck('departments.id')->toArray();
            $props['departments'] = \Illuminate\Support\Facades\DB::table('departments')
                ->whereIn('id', $userDepartments)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'short_code']);

            $propsProp->setValue($response, $props);
        }
        return $response;
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeTicketAccess($id);

        $userDepartments = Auth::user()->departments()->pluck('departments.id')->toArray();
        if ($request->filled('department_id') && !in_array($request->department_id, $userDepartments)) {
            abort(403, 'Unauthorized. You can only move tickets to departments you manage.');
        }

        parent::update($request, $id);
        return redirect()->route('manager.tickets.show', $id);
    }

    public function storeMessage(Request $request, int $id)
    {
        $this->authorizeTicketAccess($id);

        parent::storeMessage($request, $id);
        return redirect()->route('manager.tickets.show', $id);
    }

    public function storeAttachment(Request $request, int $id)
    {
        $this->authorizeTicketAccess($id);

        parent::storeAttachment($request, $id);
        return redirect()->route('manager.tickets.show', $id);
    }

    public function downloadAttachment(int $ticketId, int $attachmentId)
    {
        $this->authorizeTicketAccess($ticketId);
        
        return parent::downloadAttachment($ticketId, $attachmentId);
    }

    public function create()
    {
        $response = parent::create();
        if (is_a($response, \Inertia\Response::class)) {
            $reflection = new \ReflectionClass($response);
            $property = $reflection->getProperty('component');
            $property->setAccessible(true);
            $property->setValue($response, 'Manager/Tickets/Create');

            $propsProp = $reflection->getProperty('props');
            $propsProp->setAccessible(true);
            $props = $propsProp->getValue($response);

            $userDepartments = Auth::user()->departments()->pluck('departments.id')->toArray();
            $props['departments'] = \Illuminate\Support\Facades\DB::table('departments')
                ->whereIn('id', $userDepartments)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'short_code'])
                ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'short_code' => $d->short_code]);

            $propsProp->setValue($response, $props);
        }
        return $response;
    }

    public function store(\App\Http\Requests\StoreTicketRequest $request)
    {
        $userDepartments = Auth::user()->departments()->pluck('departments.id')->toArray();
        if ($request->filled('department_id') && !in_array($request->department_id, $userDepartments)) {
            abort(403, 'Unauthorized. You can only assign tickets to your own departments.');
        }

        parent::store($request);
        return redirect()->route('manager.tickets.index');
    }
}
