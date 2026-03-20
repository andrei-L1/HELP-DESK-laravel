<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TicketActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketMessageResource;
use App\Http\Resources\TicketAttachmentResource;
use App\Http\Resources\TicketActivityLogResource;
use App\Http\Resources\SlaPolicyResource;
use Inertia\Inertia;


class UserTicketController extends Controller
{
    /**
     * List all tickets owned by the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::with(['status', 'priority'])
            ->where('created_by', $user->id)
            ->orderByDesc('created_at');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->whereHas('status', fn ($q) => $q->where('name', $request->status));
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->whereHas('priority', fn ($q) => $q->where('name', $request->priority));
        }

        $tickets = $query->paginate(5)->withQueryString();

        $tickets->setCollection(
            TicketResource::collection($tickets->getCollection())->resource
        );


        // Simple per-user stats
        $statusCounts = Ticket::select('status_id', DB::raw('count(*) as count'))
            ->where('created_by', $user->id)
            ->groupBy('status_id')
            ->pluck('count', 'status_id');

        $statusMap = DB::table('ticket_statuses')->pluck('id', 'name');

        $stats = [
            'total'    => Ticket::where('created_by', $user->id)->count(),
            'open'     => $statusCounts[$statusMap['Open']     ?? 0] ?? 0,
            'pending'  => $statusCounts[$statusMap['Pending']  ?? 0] ?? 0,
            'resolved' => $statusCounts[$statusMap['Resolved'] ?? 0] ?? 0,
            'closed'   => $statusCounts[$statusMap['Closed']   ?? 0] ?? 0,
        ];

        $statuses   = DB::table('ticket_statuses')->where('is_active', true)->orderBy('sort_order')->pluck('name');
        $priorities = DB::table('ticket_priorities')->orderBy('sort_order')->pluck('name');

        return Inertia::render('User/Tickets/Index', [
            'tickets'    => $tickets,
            'filters'    => $request->only(['search', 'status', 'priority']),
            'statuses'   => $statuses,
            'priorities' => $priorities,
            'stats'      => $stats,
        ]);
    }

    /**
     * Show the form to create a new ticket.
     * Passes priorities with their SLA times and the user's primary department.
     */
    public function create()
    {
        $user = Auth::user();

        $categories = DB::table('ticket_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'title'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'title' => $c->title]);

        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($d) => ['id' => $d->id, 'name' => $d->name]);

        // Pull all active priorities from the DB (not hardcoded strings)
        $priorities = DB::table('ticket_priorities')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'color_hex']);

        // Build SLA map: [ priority_id => [ department_id|null => {response_time, resolution_time} ] ]
        $slaPolicies = DB::table('sla_policies')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get(['priority_id', 'department_id', 'response_time', 'resolution_time']);

        // Index by priority_id then department_id (null = global)
        $slaMap = [];
        foreach ($slaPolicies as $sla) {
            $deptKey = $sla->department_id ?? 'global';
            $slaMap[$sla->priority_id][$deptKey] = [
                'response_time'   => (int) $sla->response_time,
                'resolution_time' => (int) $sla->resolution_time,
            ];
        }

        // Attach SLA entries to each priority
        $prioritiesWithSla = $priorities->map(fn ($p) => [
            'id'        => $p->id,
            'name'      => $p->name,
            'color_hex' => $p->color_hex ?? '#64748b',
            'sla'       => $slaMap[$p->id] ?? [],
        ]);

        // Detect user's primary department to pre-fill the form
        $userDepartment = DB::table('user_departments')
            ->where('user_id', $user->id)
            ->where('is_primary', true)
            ->value('department_id');

        return Inertia::render('User/Tickets/Create', [
            'categories'      => $categories,
            'departments'     => $departments,
            'priorities'      => $prioritiesWithSla,
            'user_department' => $userDepartment,
        ]);
    }

    /**
     * Store a new ticket submitted by the user.
     */
    public function store(StoreTicketRequest $request)
    {
        $validated = $request->validated();

        $priorityId = $request->getPriorityId();
        $statusId   = $request->getStatusId();

        if (!$priorityId || !$statusId) {
            return back()->withErrors(['priority' => 'Invalid priority or status. Please contact an administrator.']);
        }

        $departmentId = isset($validated['department_id']) ? (int) $validated['department_id'] : null;
        $assignedTo   = $this->pickAutoAssignUserId($departmentId);

        $year = date('Y');
        $ticket = Cache::lock('ticket_number_' . $year, 10)->block(5, function () use ($validated, $statusId, $priorityId, $assignedTo, $year) {
            return DB::transaction(function () use ($validated, $statusId, $priorityId, $assignedTo, $year) {
                $prefix   = 'TICKET-' . $year . '-';
                $lastTicket = DB::table('tickets')->where('ticket_number', 'like', $prefix . '%')->orderByRaw('LENGTH(ticket_number) DESC')->orderBy('ticket_number', 'desc')->value('ticket_number');
                $maxNum   = $lastTicket ? (int) Str::afterLast($lastTicket, '-') : 0;

                $ticket = Ticket::create([
                    'ticket_number' => $prefix . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT),
                    'subject'       => $validated['subject'],
                    'description'   => $validated['description'],
                    'status_id'     => $statusId,
                    'priority_id'   => $priorityId,
                    'category_id'   => $validated['category_id'] ?? null,
                    'department_id' => $validated['department_id'] ?? null,
                    'created_by'    => Auth::id(),
                    'assigned_to'   => $assignedTo,
                ]);

                TicketActivityLog::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'action'    => 'ticket_created',
                    'old_value' => null,
                    'new_value' => $ticket->ticket_number,
                ]);

                return $ticket;
            });
        });

        return redirect()->route('user.tickets.show', $ticket->id)
            ->with('success', 'Ticket created successfully!');
    }

    /**
     * Show a single ticket (owned by the authenticated user).
     */
    public function show(int $id)
    {
        $user = Auth::user();

        $ticket = Ticket::with([
            'status', 'priority', 'category', 'department',
            'messages' => fn ($q) => $q->where('is_internal', false),
            'attachments', 'activityLogs',
        ])->where('created_by', $user->id)->findOrFail($id);

        // Load user names for messages / logs
        $userIds = collect($ticket->messages->pluck('user_id'))
            ->merge($ticket->activityLogs->pluck('user_id'))
            ->filter()
            ->unique();

        $userNames = DB::table('users')->whereIn('id', $userIds)->get(['id', 'first_name', 'last_name'])
            ->mapWithKeys(fn ($u) => [$u->id => trim($u->first_name . ' ' . $u->last_name) ?: "User #{$u->id}"]);

        return Inertia::render('User/Tickets/Show', [
            'ticket'        => (new TicketResource($ticket))->resolve(),
            'messages'      => TicketMessageResource::collection($ticket->messages)->resolve(),
            'attachments'   => TicketAttachmentResource::collection($ticket->attachments)->resolve(),
            'activity_logs' => TicketActivityLogResource::collection($ticket->activityLogs->take(30))->resolve(),
        ]);


    }

    /**
     * Store a reply message from the user.
     */
    public function storeMessage(Request $request, int $id)
    {
        $user   = Auth::user();
        $ticket = Ticket::where('created_by', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:10000',
        ]);

        TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user->id,
            'is_internal' => false,
            'body'        => $validated['body'],
        ]);

        TicketActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'action'    => 'message_added',
            'old_value' => null,
            'new_value' => 'User reply',
        ]);

        return redirect()->route('user.tickets.show', $ticket->id);
    }

    /**
     * Upload an attachment to the ticket (by the owner).
     */
    public function storeAttachment(Request $request, int $id)
    {
        $user   = Auth::user();
        $ticket = Ticket::where('created_by', $user->id)->findOrFail($id);

        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file       = $request->file('file');
        $dir        = 'ticket-attachments/' . $ticket->id;
        $path       = $file->store($dir, 'local');
        $storedName = basename($path);

        TicketAttachment::create([
            'ticket_id'   => $ticket->id,
            'message_id'  => null,
            'file_name'   => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'file_path'   => $path,
            'file_size'   => $file->getSize(),
            'mime_type'   => $file->getMimeType(),
            'uploaded_by' => $user->id,
        ]);

        TicketActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'action'    => 'attachment_added',
            'old_value' => null,
            'new_value' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('user.tickets.show', $ticket->id);
    }

    /**
     * Download an attachment (only if the ticket belongs to the user).
     */
    public function downloadAttachment(int $ticketId, int $attachmentId)
    {
        $user   = Auth::user();
        $ticket = Ticket::where('created_by', $user->id)->findOrFail($ticketId);

        $attachment = TicketAttachment::where('ticket_id', $ticket->id)
            ->where('id', $attachmentId)
            ->firstOrFail();

        $path = Storage::disk('local')->path($attachment->file_path);

        if (!is_file($path)) {
            abort(404);
        }

        return response()->download($path, $attachment->file_name, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }


    private function pickAutoAssignUserId(?int $departmentId = null): ?int
    {
        $openStatusId = DB::table('ticket_statuses')->where('name', 'Open')->value('id');
        if (!$openStatusId) {
            return null;
        }

        $query = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->whereIn('roles.name', ['manager', 'agent']);

        if ($departmentId) {
            $query->join('user_departments', 'users.id', '=', 'user_departments.user_id')
                  ->where('user_departments.department_id', $departmentId);
        }

        $assignableIds = $query->pluck('users.id')->all();

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
}
