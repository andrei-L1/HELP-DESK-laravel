<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TicketActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketMessageResource;
use App\Http\Resources\TicketAttachmentResource;
use App\Http\Resources\TicketActivityLogResource;
use App\Http\Resources\SlaPolicyResource;
use Illuminate\Support\Facades\Auth;


class AdminTicketController extends Controller
{
    /**
     * Display a paginated list of ALL tickets for admins.
     */

    public function all(Request $request)
    {
        return $this->getTickets($request, null, 'all', 'Admin/Tickets/All');
    }
    public function index(Request $request)
    {
        return $this->getTickets($request, null, 'index', 'Admin/Tickets/Index'); 
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
        $categories = DB::table('ticket_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'title'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'title' => $c->title]);
        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'short_code'])
            ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'short_code' => $d->short_code]);
        return Inertia::render('Admin/Tickets/Create', [
            'categories'  => $categories,
            'departments' => $departments,
        ]);
    }

    /**
     * Show a single ticket (detail page) with assign option.
     */
    public function show(int $id)
    {
        $ticket = Ticket::with([
            'status', 'priority', 'category', 'department', 
            'creator', 'assignee', 'slaPolicy',
            'messages.user', 'attachments', 'activityLogs.user'
        ])->findOrFail($id);

        $users = $this->getAssignableUsers($ticket->department_id);
        
        $statuses = DB::table('ticket_statuses')->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'title']);
        $priorities = DB::table('ticket_priorities')->orderBy('sort_order')->get(['id', 'name']);
        $departments = DB::table('departments')->whereNull('deleted_at')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'short_code']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket'        => (new TicketResource($ticket))->resolve(),
            'messages'      => TicketMessageResource::collection($ticket->messages)->resolve(),
            'attachments'   => TicketAttachmentResource::collection($ticket->attachments)->resolve(),
            'activity_logs' => TicketActivityLogResource::collection($ticket->activityLogs->take(50))->resolve(),
            'sla_policy'    => $ticket->slaPolicy ? (new SlaPolicyResource($ticket->slaPolicy))->resolve() : null,
            'statuses'      => $statuses,
            'priorities'    => $priorities,
            'departments'   => $departments,
            'users'         => $users,
        ]);
    }

    /**

     * Update ticket (assign, status, department). Only managers and agents can be assigned.
     */
    public function update(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);

        // Admins are globally scoped, so no department auth check is enforced here.
        // If you need per-department admin restriction, override this method in a subclass.

        // Resolve the effective department FIRST — it may be changing in this same request.
        // We must validate assigned_to against the *new* department, not the old one.
        $effectiveDepartmentId = $request->filled('department_id')
            ? (int) $request->input('department_id')
            : $ticket->department_id;

        $assignableIds = $this->getAssignableUserIds($effectiveDepartmentId);

        $validated = $request->validate([
            'assigned_to'   => ['nullable', 'integer', Rule::in($assignableIds)],
            'status_id'     => 'nullable|integer|exists:ticket_statuses,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'priority_id'   => 'nullable|integer|exists:ticket_priorities,id',
        ]);

        $oldStatusId     = $ticket->status_id;
        $newStatusId     = $validated['status_id'] ?? $ticket->status_id;
        $oldDepartmentId = $ticket->department_id;
        $newDepartmentId = array_key_exists('department_id', $validated)
            ? $validated['department_id']
            : $ticket->department_id;

        // ── Assignment change ────────────────────────────────────────────────
        if (array_key_exists('assigned_to', $validated)) {
            $oldAssigned = $ticket->assigned_to;
            $newAssigned = $validated['assigned_to'] ?? null;

            if ($oldAssigned != $newAssigned) {
                // Resolve human-readable names so the log is auditable after soft-deletes
                $oldName = $oldAssigned
                    ? (\App\Models\User::withTrashed()->find($oldAssigned)?->first_name . ' '
                       . \App\Models\User::withTrashed()->find($oldAssigned)?->last_name)
                    : 'Unassigned';
                $newName = $newAssigned
                    ? (\App\Models\User::withTrashed()->find($newAssigned)?->first_name . ' '
                       . \App\Models\User::withTrashed()->find($newAssigned)?->last_name)
                    : 'Unassigned';

                $ticket->assigned_to = $newAssigned;
                $this->logTicketActivity($ticket->id, 'assignment_changed', trim($oldName), trim($newName));
            }
        }

        // ── Status change ────────────────────────────────────────────────────
        if ($newStatusId && $newStatusId != $oldStatusId) {
            $ticket->status_id = $newStatusId;
            $statusName    = DB::table('ticket_statuses')->where('id', $newStatusId)->value('name');
            $oldStatusName = DB::table('ticket_statuses')->where('id', $oldStatusId)->value('name');

            if ($statusName === 'Resolved') {
                $ticket->resolved_at = $ticket->resolved_at ?? now();
                $ticket->resolver_id = Auth::id();
            }
            if ($statusName === 'Closed') {
                $ticket->closed_at = $ticket->closed_at ?? now();
                $ticket->closed_by = Auth::id();
            }

            $this->logTicketActivity($ticket->id, 'status_changed', $oldStatusName ?? 'Unknown', $statusName ?? 'Unknown');
        }

        // ── Priority change ──────────────────────────────────────────────────
        if ($request->filled('priority_id') && $request->priority_id != $ticket->priority_id) {
            $oldPriorityName = DB::table('ticket_priorities')->where('id', $ticket->priority_id)->value('name') ?? 'Unknown';
            $newPriorityName = DB::table('ticket_priorities')->where('id', $request->priority_id)->value('name') ?? 'Unknown';

            $ticket->priority_id = $request->priority_id;
            $this->logTicketActivity($ticket->id, 'priority_changed', $oldPriorityName, $newPriorityName);
        }

        // ── Department change ────────────────────────────────────────────────
        if ($oldDepartmentId != $newDepartmentId) {
            $oldDeptName = DB::table('departments')->where('id', $oldDepartmentId)->value('name') ?? 'None';
            $newDeptName = DB::table('departments')->where('id', $newDepartmentId)->value('name') ?? 'None';

            $ticket->department_id = $newDepartmentId;
            $this->logTicketActivity($ticket->id, 'department_changed', $oldDeptName, $newDeptName);
        }

        $ticket->save();

        event(new \App\Events\TicketUpdated('updated', $ticket->id, $ticket->created_by));

        return redirect()->route('admin.tickets.show', $ticket->id);
    }

    /**
     * Add a reply or internal note to the ticket.
     */
    public function storeMessage(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $validated = $request->validate([
            'body'        => 'required|string|max:10000',
            'is_internal' => 'boolean',
        ]);
        $isInternal = $request->boolean('is_internal');

        $message = TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'is_internal' => $isInternal,
            'body'        => $validated['body'],
        ]);

        if (!$ticket->first_response_at && !$isInternal && Auth::id() !== $ticket->created_by) {
            $ticket->first_response_at = now();
            $ticket->save();
        }

        $this->logTicketActivity($ticket->id, 'message_added', null, $isInternal ? 'Internal note' : 'Reply');

        event(new \App\Events\TicketUpdated('replied', $ticket->id, $ticket->created_by));

        return redirect()->route('admin.tickets.show', $ticket->id);
    }

    /**
     * Upload an attachment to the ticket (optionally linked to a message).
     */
    public function storeAttachment(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $validated = $request->validate([
            'file'       => 'required|file|max:10240', // 10MB
            'message_id' => 'nullable|integer|exists:ticket_messages,id',
        ]);
        $file = $request->file('file');
        $dir = 'ticket-attachments/' . $ticket->id;
        $path = $file->store($dir, 'local');
        $storedName = basename($path);

        TicketAttachment::create([
            'ticket_id'   => $ticket->id,
            'message_id'  => $validated['message_id'] ?? null,
            'file_name'   => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'file_path'   => $path,
            'file_size'   => $file->getSize(),
            'mime_type'   => $file->getMimeType(),
            'uploaded_by' => Auth::id(),
        ]);

        $this->logTicketActivity($ticket->id, 'attachment_added', null, $file->getClientOriginalName());

        return redirect()->route('admin.tickets.show', $ticket->id);
    }

    /**
     * Download a ticket attachment (admin only).
     */
    public function downloadAttachment(int $ticketId, int $attachmentId)
    {
        $attachment = TicketAttachment::where('ticket_id', $ticketId)->where('id', $attachmentId)->firstOrFail();
        $path = Storage::disk('local')->path($attachment->file_path);
        if (!is_file($path)) {
            abort(404);
        }
        return response()->download($path, $attachment->file_name, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }

    public function store(\App\Http\Requests\StoreTicketRequest $request)
    {
        $validated = $request->validated();
        
        $priorityId = $request->getPriorityId();
        $statusId = $request->getStatusId();

        if (!$priorityId || !$statusId) {
            return back()->withErrors(['priority' => 'Invalid priority or status configuration. Run seeders.']);
        }

        $departmentId = isset($validated['department_id']) ? (int) $validated['department_id'] : null;
        $assignedTo = $this->pickAutoAssignUserId($departmentId);

        $year = date('Y');
        $ticket = Cache::lock('ticket_number_' . $year, 10)->block(5, function () use ($validated, $statusId, $priorityId, $assignedTo, $year) {
            return DB::transaction(function () use ($validated, $statusId, $priorityId, $assignedTo, $year) {
                $prefix = 'TICKET-' . $year . '-';
                $lastTicket = DB::table('tickets')
                    ->where('ticket_number', 'like', $prefix . '%')
                    ->orderByRaw('LENGTH(ticket_number) DESC')
                    ->orderBy('ticket_number', 'desc')
                    ->value('ticket_number');
                $maxNum = $lastTicket ? (int) Str::afterLast($lastTicket, '-') : 0;
                $ticketNumber = $prefix . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);

                $ticket = Ticket::create([
                    'ticket_number' => $ticketNumber,
                    'subject'       => $validated['subject'],
                    'description'   => $validated['description'],
                    'status_id'     => $statusId,
                    'priority_id'   => $priorityId,
                    'category_id'   => $validated['category_id'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                    'created_by'    => Auth::id(),
                    'assigned_to'   => $assignedTo,
                ]);

                $this->logTicketActivity($ticket->id, 'ticket_created', null, $ticketNumber);

                // Log initial assignment with a human-readable name
                if ($assignedTo) {
                    $assigneeName = \App\Models\User::find($assignedTo);
                    $assigneeLabel = $assigneeName
                        ? trim($assigneeName->first_name . ' ' . $assigneeName->last_name)
                        : "User #{$assignedTo}";
                    $this->logTicketActivity($ticket->id, 'assignment_changed', 'Unassigned', $assigneeLabel);
                }

                event(new \App\Events\TicketUpdated('created', $ticket->id, $ticket->created_by));

                return $ticket;
            });
        });

        return redirect()->route('admin.tickets.index');
    }

    /**
     * Users who can be assigned tickets: managers and agents only.
     */
    protected function getAssignableUsers(?int $departmentId = null): array
    {
        $query = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', ['manager', 'agent']);
            
        if ($departmentId !== null) {
            $query->join('user_departments', 'users.id', '=', 'user_departments.user_id')
                  ->where('user_departments.department_id', $departmentId);
        }

        return $query->orderBy('users.first_name')
            ->orderBy('users.last_name')
            ->distinct()
            ->get(['users.id', 'users.first_name', 'users.last_name'])
            ->map(fn ($u) => [
                'id'   => $u->id,
                'name' => trim("{$u->first_name} {$u->last_name}") ?: "User #{$u->id}",
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    protected function getAssignableUserIds(?int $departmentId = null): array
    {
        $query = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', ['manager', 'agent']);

        if ($departmentId !== null) {
            $query->join('user_departments', 'users.id', '=', 'user_departments.user_id')
                  ->where('user_departments.department_id', $departmentId);
        }

        return $query->distinct()->pluck('users.id')->unique()->values()->all();
    }

    /**
     * Pick a user for auto-assign: the assignable user with the fewest open tickets (round-robin style).
     * Returns null if no department is specified — we never auto-assign across departments.
     */
    protected function pickAutoAssignUserId(?int $departmentId = null): ?int
    {
        // Never auto-assign if no department is given — we'd be picking from ALL agents globally
        // which would incorrectly assign billing agents to technical support tickets, etc.
        if ($departmentId === null) {
            return null;
        }

        $openStatusId = DB::table('ticket_statuses')->where('name', 'Open')->value('id');
        if (!$openStatusId) {
            return null;
        }

        $assignableIds = $this->getAssignableUserIds($departmentId);
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
    protected function getTickets(Request $request, ?string $statusFilter, string $view, string $component, ?int $assignedTo = null)
    {
        $query = Ticket::with(['status', 'priority', 'creator', 'assignee']);

        // Handle Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'updated':
                $query->orderByDesc('updated_at');
                break;
            case 'priority_desc':
                $query->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
                    ->orderBy('ticket_priorities.sort_order', 'asc')
                    ->select('tickets.*');
                break;
            case 'latest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

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

        // Apply department filter
        if ($request->filled('department')) {
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

        $tickets->setCollection(
            TicketResource::collection($tickets->getCollection())->resource
        );


        // Optimize stats query
        $statusCounts = Ticket::select('status_id', DB::raw('count(*) as count'))->groupBy('status_id')->pluck('count', 'status_id');
        $priorityCounts = Ticket::select('priority_id', DB::raw('count(*) as count'))->groupBy('priority_id')->pluck('count', 'priority_id');

        $statusMap = DB::table('ticket_statuses')->pluck('id', 'name');
        $priorityMap = DB::table('ticket_priorities')->pluck('id', 'name');

        $stats = [
            'total'     => Ticket::count(),
            'open'      => $statusCounts[$statusMap['Open'] ?? 0] ?? 0,
            'pending'   => $statusCounts[$statusMap['Pending'] ?? 0] ?? 0,
            'resolved'  => $statusCounts[$statusMap['Resolved'] ?? 0] ?? 0,
            'closed'    => $statusCounts[$statusMap['Closed'] ?? 0] ?? 0,
            'urgent'    => $priorityCounts[$priorityMap['Urgent'] ?? 0] ?? 0,
            'high'      => $priorityCounts[$priorityMap['High'] ?? 0] ?? 0,
            'medium'    => $priorityCounts[$priorityMap['Medium'] ?? 0] ?? 0,
            'low'       => $priorityCounts[$priorityMap['Low'] ?? 0] ?? 0,
        ];

        // Retrieve dropdown filter maps
        $statuses = DB::table('ticket_statuses')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $priorities = DB::table('ticket_priorities')->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $categories = DB::table('ticket_categories')->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'title']);
        $departments = DB::table('departments')->whereNull('deleted_at')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'short_code']);

        return Inertia::render($component, [
            'tickets'     => $tickets,
            'filters'     => $request->only(['search', 'status', 'priority', 'department', 'sort']),
            'statuses'    => $statuses,
            'priorities'  => $priorities,
            'categories'  => $categories,
            'departments' => $departments,
            'stats'       => $stats,
            'view'        => $view,
        ]);
    }



    private function logTicketActivity(int $ticketId, string $action, ?string $oldValue, ?string $newValue, ?array $details = null): void
    {
        TicketActivityLog::create([
            'ticket_id' => $ticketId,
            'user_id'   => Auth::id(),
            'action'    => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'details'   => $details,
        ]);
    }

    /**
     * Bulk remove tickets.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No tickets selected.');
        }

        Ticket::whereIn('id', $ids)->delete();

        foreach ($ids as $id) {
            // We don't have the creator ID handy without querying, but broadcasting to staff is enough for bulk deletes
            event(new \App\Events\TicketUpdated('deleted', $id, null));
        }

        return redirect()->back()->with('success', count($ids) . ' tickets deleted successfully.');
    }

    /**
     * Bulk update tickets status.
     */
    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $statusId = $request->input('status_id');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No tickets selected.');
        }

        if ($statusId) {
            Ticket::whereIn('id', $ids)->update(['status_id' => $statusId]);
            foreach ($ids as $id) {
                event(new \App\Events\TicketUpdated('updated', $id, null));
            }
        }

        return redirect()->back()->with('success', count($ids) . ' tickets updated successfully.');
    }
}