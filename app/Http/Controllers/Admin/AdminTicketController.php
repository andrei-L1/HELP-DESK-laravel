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
        $ticketRow = DB::table('tickets')
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->leftJoin('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->leftJoin('departments', 'tickets.department_id', '=', 'departments.id')
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
                'tickets.status_id',
                'tickets.priority_id',
                'tickets.category_id',
                'tickets.department_id',
                'tickets.due_at',
                'tickets.first_response_at',
                'tickets.resolved_at',
                'tickets.closed_at',
                'ticket_statuses.name as status',
                'ticket_priorities.name as priority',
                'ticket_categories.title as category_title',
                'departments.name as department_name',
                DB::raw("CONCAT(COALESCE(created_by_user.first_name, ''), ' ', COALESCE(created_by_user.last_name, '')) as created_by"),
                DB::raw("CONCAT(COALESCE(assigned_to_user.first_name, ''), ' ', COALESCE(assigned_to_user.last_name, '')) as assigned_to_name"),
                'tickets.created_at',
                'tickets.updated_at'
            )
            ->first();

        if (!$ticketRow) {
            abort(404);
        }

        $users = $this->getAssignableUsers();

        $messages = TicketMessage::query()
            ->where('ticket_id', $id)
            ->with('user:id,first_name,last_name')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'body'        => $m->body,
                'is_internal' => $m->is_internal,
                'author'      => $m->user ? trim($m->user->first_name . ' ' . $m->user->last_name) : 'Unknown',
                'created_at'  => $m->created_at?->toDateTimeString(),
            ]);

        $attachments = TicketAttachment::query()
            ->where('ticket_id', $id)
            ->get(['id', 'file_name', 'file_size', 'uploaded_at'])
            ->map(fn ($a) => [
                'id'          => $a->id,
                'file_name'   => $a->file_name,
                'file_size'   => $a->file_size,
                'uploaded_at' => $a->uploaded_at?->toDateTimeString(),
            ]);

        $activityLogs = TicketActivityLog::query()
            ->where('ticket_id', $id)
            ->with('user:id,first_name,last_name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($l) => [
                'id'         => $l->id,
                'action'     => $l->action,
                'old_value'  => $l->old_value,
                'new_value'  => $l->new_value,
                'user_name'  => $l->user ? trim($l->user->first_name . ' ' . $l->user->last_name) : 'Unknown',
                'created_at' => $l->created_at?->toDateTimeString(),
            ]);

        $slaPolicy = null;
        if ($ticketRow->priority_id) {
            $sla = $this->findSlaPolicy($ticketRow->priority_id, $ticketRow->department_id);
            if ($sla) {
                $slaPolicy = [
                    'name'            => $sla->name,
                    'response_time'   => (int) $sla->response_time,
                    'resolution_time' => (int) $sla->resolution_time,
                ];
            }
        }

        $statuses = DB::table('ticket_statuses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'title'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'title' => $s->title]);

        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'short_code'])
            ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'short_code' => $d->short_code]);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => [
                'id'                 => $ticketRow->id,
                'ticket_number'      => $ticketRow->ticket_number,
                'subject'            => $ticketRow->subject,
                'description'        => $ticketRow->description,
                'status'             => $ticketRow->status ?? 'Unknown',
                'status_id'          => $ticketRow->status_id,
                'priority'           => $ticketRow->priority ?? 'Unknown',
                'category_id'        => $ticketRow->category_id,
                'category_title'     => $ticketRow->category_title ?? null,
                'department_id'      => $ticketRow->department_id,
                'department_name'    => $ticketRow->department_name ?? null,
                'created_by'         => trim($ticketRow->created_by) ?: 'Unknown',
                'assigned_to_id'     => $ticketRow->assigned_to_id,
                'assigned_to_name'   => trim($ticketRow->assigned_to_name) ?: null,
                'due_at'             => $ticketRow->due_at ? \Carbon\Carbon::parse($ticketRow->due_at)->toDateTimeString() : null,
                'first_response_at'  => $ticketRow->first_response_at ? \Carbon\Carbon::parse($ticketRow->first_response_at)->toDateTimeString() : null,
                'resolved_at'        => $ticketRow->resolved_at ? \Carbon\Carbon::parse($ticketRow->resolved_at)->toDateTimeString() : null,
                'closed_at'          => $ticketRow->closed_at ? \Carbon\Carbon::parse($ticketRow->closed_at)->toDateTimeString() : null,
                'created_at'         => $ticketRow->created_at ? \Carbon\Carbon::parse($ticketRow->created_at)->toDateTimeString() : null,
                'updated_at'         => $ticketRow->updated_at ? \Carbon\Carbon::parse($ticketRow->updated_at)->toDateTimeString() : null,
            ],
            'statuses'      => $statuses,
            'departments'   => $departments,
            'sla_policy'   => $slaPolicy,
            'messages'     => $messages,
            'attachments'  => $attachments,
            'activity_logs' => $activityLogs,
            'users'        => $users,
        ]);
    }

    /**
     * Update ticket (assign, status, department). Only managers and agents can be assigned.
     */
    public function update(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $assignableIds = $this->getAssignableUserIds();

        $validated = $request->validate([
            'assigned_to'   => [
                'nullable',
                'integer',
                Rule::in($assignableIds),
            ],
            'status_id'     => 'nullable|integer|exists:ticket_statuses,id',
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $oldStatusId   = $ticket->status_id;
        $newStatusId   = $validated['status_id'] ?? $ticket->status_id;
        $oldDepartmentId = $ticket->department_id;
        $newDepartmentId = array_key_exists('department_id', $validated) ? $validated['department_id'] : $ticket->department_id;

        if (array_key_exists('assigned_to', $validated)) {
            $oldAssigned = $ticket->assigned_to;
            $ticket->assigned_to = $validated['assigned_to'] ?? null;
            if ($oldAssigned != $ticket->assigned_to) {
                $this->logTicketActivity($ticket->id, 'assignment_changed', (string) $oldAssigned, (string) $ticket->assigned_to);
            }
        }

        if ($newStatusId && $newStatusId != $oldStatusId) {
            $ticket->status_id = $newStatusId;
            $statusName = DB::table('ticket_statuses')->where('id', $newStatusId)->value('name');
            if ($statusName === 'Resolved') {
                $ticket->resolved_at = $ticket->resolved_at ?? now();
                $ticket->resolver_id = Auth::id();
            }
            if ($statusName === 'Closed') {
                $ticket->closed_at = $ticket->closed_at ?? now();
                $ticket->closed_by = Auth::id();
            }
            $oldStatusName = DB::table('ticket_statuses')->where('id', $oldStatusId)->value('name');
            $this->logTicketActivity($ticket->id, 'status_changed', $oldStatusName ?? '', $statusName ?? '');
        }

        if ($oldDepartmentId != $newDepartmentId) {
            $ticket->department_id = $newDepartmentId;
            $this->logTicketActivity($ticket->id, 'department_changed', (string) $oldDepartmentId, (string) $newDepartmentId);
        }

        $ticket->save();

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

        if (!$ticket->first_response_at && !$isInternal) {
            $ticket->first_response_at = now();
            $ticket->save();
        }

        $this->logTicketActivity($ticket->id, 'message_added', null, $isInternal ? 'Internal note' : 'Reply');

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'       => 'required|string|max:200',
            'description'   => 'required|string',
            'priority'      => 'required|string|in:low,medium,high',
            'category_id'   => 'nullable|integer|exists:ticket_categories,id',
            'department_id' => 'nullable|integer|exists:departments,id',
        ]);

        $priorityName = ucfirst(strtolower($validated['priority']));
        $priorityId   = DB::table('ticket_priorities')->where('name', $priorityName)->value('id');
        $statusId     = DB::table('ticket_statuses')->where('name', 'Open')->value('id');

        if (!$priorityId || !$statusId) {
            return back()->withErrors(['priority' => 'Invalid priority or status configuration. Run seeders.']);
        }

        $assignedTo = $this->pickAutoAssignUserId();
        $departmentId = isset($validated['department_id']) ? (int) $validated['department_id'] : null;
        $dueAt      = $this->computeDueAtFromSla($priorityId, $departmentId);

        $year = date('Y');
        $ticket = Cache::lock('ticket_number_' . $year, 10)->block(5, function () use ($validated, $statusId, $priorityId, $assignedTo, $dueAt, $year) {
            return DB::transaction(function () use ($validated, $statusId, $priorityId, $assignedTo, $dueAt, $year) {
                $prefix = 'TICKET-' . $year . '-';
                // Include soft-deleted so we never reuse a number.
                $existing = DB::table('tickets')
                    ->where('ticket_number', 'like', $prefix . '%')
                    ->pluck('ticket_number');
                $maxNum = $existing->isEmpty()
                    ? 0
                    : $existing->max(fn ($num) => (int) Str::afterLast($num, '-'));
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
                    'due_at'        => $dueAt,
                ]);

                $this->logTicketActivity($ticket->id, 'ticket_created', null, $ticketNumber);
                if ($assignedTo) {
                    $this->logTicketActivity($ticket->id, 'assignment_changed', null, (string) $assignedTo);
                }

                return $ticket;
            });
        });

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
                'tickets.created_at',
                'tickets.status_id',
                'tickets.priority_id',
                'tickets.department_id'
            )
            ->orderByDesc('tickets.created_at');

        // Apply status filter from URL/query
        if ($request->filled('status')) {
            $query->where('ticket_statuses.name', $request->status);
        }
        // Apply view-specific hard filter
        else if ($statusFilter !== null) {
            $query->where('ticket_statuses.name', $statusFilter);
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('ticket_priorities.name', $request->priority);
        }

        // Apply department filter
        if ($request->filled('department')) {
            $query->where('tickets.department_id', $request->department);
        }

        // Apply assigned filter for assigned view
        if ($view === 'assigned' && $assignedTo) {
            $query->where('tickets.assigned_to', $assignedTo);
        }

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('tickets.ticket_number', 'like', "%{$search}%")
                ->orWhere('tickets.subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Transform collection
        $tickets->getCollection()->transform(function ($ticket) {
            return [
                'id'            => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject'       => $ticket->subject,
                'status'        => $ticket->status ?? 'Unknown',
                'priority'      => $ticket->priority ?? 'Unknown',
                'created_by'    => trim($ticket->created_by) ?: 'Unknown',
                'assigned_to'   => trim($ticket->assigned_to) ?: 'Unassigned',
                'created_at'    => \Carbon\Carbon::parse($ticket->created_at)->toDateTimeString(),
            ];
        });

        // Get stats for dashboard - using your migration structure
        $stats = [
            'total'     => DB::table('tickets')->count(),
            'open'      => DB::table('tickets')
                            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                            ->where('ticket_statuses.name', 'Open')
                            ->count(),
            'pending'   => DB::table('tickets')
                            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                            ->where('ticket_statuses.name', 'Pending')
                            ->count(),
            'resolved'  => DB::table('tickets')
                            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                            ->where('ticket_statuses.name', 'Resolved')
                            ->count(),
            'closed'    => DB::table('tickets')
                            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
                            ->where('ticket_statuses.name', 'Closed')
                            ->count(),
            'urgent'    => DB::table('tickets')
                            ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
                            ->where('ticket_priorities.name', 'Urgent')
                            ->count(),
            'high'      => DB::table('tickets')
                            ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
                            ->where('ticket_priorities.name', 'High')
                            ->count(),
        ];

        // Get statuses for dropdown - only active ones
        $statuses = DB::table('ticket_statuses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        // Get priorities for dropdown - all priorities (no is_active column)
        $priorities = DB::table('ticket_priorities')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        // Get categories for dropdown - only active ones
        $categories = DB::table('ticket_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'title'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'title' => $c->title])
            ->values()
            ->all();

        // Get departments for dropdown - all active departments
        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'short_code'])
            ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name, 'short_code' => $d->short_code])
            ->values()
            ->all();

        return Inertia::render($component, [
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

    /**
     * Find SLA policy: department-specific first, then global (department_id null).
     */
    private function findSlaPolicy(int $priorityId, ?int $departmentId): ?object
    {
        if ($departmentId) {
            $sla = DB::table('sla_policies')
                ->where('priority_id', $priorityId)
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();
            if ($sla) {
                return $sla;
            }
        }
        return DB::table('sla_policies')
            ->where('priority_id', $priorityId)
            ->whereNull('department_id')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();
    }

    private function computeDueAtFromSla(int $priorityId, ?int $departmentId = null): ?string
    {
        $sla = $this->findSlaPolicy($priorityId, $departmentId);
        if (!$sla || !$sla->resolution_time) {
            return null;
        }
        return now()->addMinutes((int) $sla->resolution_time)->toDateTimeString();
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
}