<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketActivityLog;
use App\Models\TicketMessage;
use App\Models\TicketAttachment;
use App\Http\Requests\StoreTicketRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketMessageResource;
use App\Http\Resources\TicketAttachmentResource;
use App\Http\Resources\TicketActivityLogResource;
use App\Http\Resources\SlaPolicyResource;
use Inertia\Inertia;


class TicketController extends Controller
{
    protected function agentTicketsQuery()
    {
        return Ticket::where('assigned_to', Auth::id());
    }

    public function index(Request $request)
    {
        $tickets = $this->agentTicketsQuery()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->whereHas('status', fn($q) => $q->where('name', $status)->orWhere('id', $status));
            })
            ->when($request->priority, function ($query, $priority) {
                $query->whereHas('priority', fn($q) => $q->where('name', $priority)->orWhere('id', $priority));
            })
            ->with(['status', 'priority', 'creator'])
            ->latest('updated_at')
            ->paginate(15);


        $tickets->setCollection(
            TicketResource::collection($tickets->getCollection())->resource
        );

        return Inertia::render('Agent/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status', 'priority']),
        ]);

    }

    public function open()
    {
        return $this->filteredByStatus('Open');
    }

    public function pending()
    {
        return $this->filteredByStatus('Pending');
    }

    public function resolved()
    {
        return $this->filteredByStatus('Resolved');
    }

    public function closed()
    {
        return $this->filteredByStatus('Closed');
    }

    protected function filteredByStatus(string $statusName)
    {
        $status = TicketStatus::where('name', $statusName)->firstOrFail();

        $tickets = $this->agentTicketsQuery()
            ->where('status_id', $status->id)
            ->with(['status', 'priority', 'creator'])
            ->latest('updated_at')
            ->paginate(15);

        $tickets->setCollection(
            TicketResource::collection($tickets->getCollection())->resource
        );

        return Inertia::render('Agent/Tickets/Index', [
            'tickets' => $tickets,
            'currentStatus' => $statusName,
        ]);

    }

    public function create()
    {
        $categories = DB::table('ticket_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'title']);

        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $priorities = DB::table('ticket_priorities')
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return Inertia::render('Agent/Tickets/Create', [
            'categories'  => $categories,
            'departments' => $departments,
            'priorities'  => $priorities,
        ]);
    }

    public function store(StoreTicketRequest $request)
    {
        $validated = $request->validated();
        $priorityId = $request->getPriorityId();
        $statusId = $request->getStatusId();

        if (!$priorityId || !$statusId) {
            return back()->withErrors(['priority' => 'Invalid priority or status. Please contact an administrator.']);
        }

        $departmentId = isset($validated['department_id']) ? (int) $validated['department_id'] : null;
        $slaPolicy    = $this->findSlaPolicy($priorityId, $departmentId);
        $dueAt        = $slaPolicy ? now()->addMinutes((int) $slaPolicy->resolution_time)->toDateTimeString() : null;
        $assignedTo   = $this->pickAutoAssignUserId($departmentId);

        $year = date('Y');
        $ticket = Cache::lock('ticket_number_' . $year, 10)->block(5, function () use ($validated, $statusId, $priorityId, $departmentId, $dueAt, $assignedTo, $slaPolicy, $year) {
            return DB::transaction(function () use ($validated, $statusId, $priorityId, $departmentId, $dueAt, $assignedTo, $slaPolicy, $year) {
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
                    'department_id' => $departmentId,
                    'sla_policy_id' => $slaPolicy?->id,
                    'created_by'    => Auth::id(),
                    'assigned_to'   => $assignedTo,
                    'due_at'        => $dueAt,
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

        return redirect()->route('agent.tickets.show', $ticket->id)
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeAgentAccess($ticket);

        $ticket->load(['status', 'priority', 'category', 'department', 'creator', 'messages.user', 'attachments', 'activityLogs.user', 'slaPolicy']);

        return Inertia::render('Agent/Tickets/Show', [
            'ticket'        => (new TicketResource($ticket))->resolve(),
            'messages'      => TicketMessageResource::collection($ticket->messages)->resolve(),
            'attachments'   => TicketAttachmentResource::collection($ticket->attachments)->resolve(),
            'activity_logs' => TicketActivityLogResource::collection($ticket->activityLogs->take(50))->resolve(),
            'sla_policy'    => $ticket->slaPolicy ? (new SlaPolicyResource($ticket->slaPolicy))->resolve() : null,
        ]);


    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeAgentAccess($ticket);

        return Inertia::render('Agent/Tickets/Edit', [
            'ticket' => $ticket,
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeAgentAccess($ticket);

        // Validation + update logic

        return redirect()->route('agent.tickets.show', $ticket)
            ->with('success', 'Ticket updated.');
    }

    protected function authorizeAgentAccess(Ticket $ticket)
    {
        if ($ticket->assigned_to !== Auth::id()) {
            abort(403, 'You do not have access to this ticket.');
        }
    }

    // Bonus: common agent actions (you can call from frontend via Inertia form)

    public function reply(Request $request, Ticket $ticket)
    {
        $this->authorizeAgentAccess($ticket);

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
        }

        $pendingStatusId = TicketStatus::where('name', 'Pending')->value('id');
        if ($pendingStatusId) {
             $ticket->status_id = $pendingStatusId;
        }
        
        $ticket->save();

        TicketActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'message_added',
            'old_value' => null,
            'new_value' => $isInternal ? 'Internal note' : 'Reply',
        ]);

        return back()->with('success', 'Reply added.');
    }

    public function resolve(Ticket $ticket)
    {
        $this->authorizeAgentAccess($ticket);

        $ticket->update([
            'status_id' => TicketStatus::where('name', 'Resolved')->firstOrFail()->id,
        ]);

        return back()->with('success', 'Ticket marked as resolved.');
    }

    public function reopen(Ticket $ticket)
    {
        $this->authorizeAgentAccess($ticket);

        $ticket->update([
            'status_id' => TicketStatus::where('name', 'Open')->firstOrFail()->id,
        ]);

        return back()->with('success', 'Ticket reopened.');
    }

    public function storeAttachment(Request $request, int $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorizeAgentAccess($ticket);

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

        TicketActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'action'    => 'attachment_added',
            'old_value' => null,
            'new_value' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('agent.tickets.show', $ticket->id);
    }

    public function downloadAttachment(int $ticketId, int $attachmentId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $this->authorizeAgentAccess($ticket);

        $attachment = TicketAttachment::where('ticket_id', $ticketId)->where('id', $attachmentId)->firstOrFail();
        $path = Storage::disk('local')->path($attachment->file_path);
        if (!is_file($path)) {
            abort(404);
        }
        return response()->download($path, $attachment->file_name, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }

    // ─── Private helpers ────────────────────────────────────────────────

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

    private function pickAutoAssignUserId(?int $departmentId = null): ?int
    {
        if ($departmentId === null) {
            return null;
        }

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

        $assignableIds = $query->distinct()->pluck('users.id')->unique()->values()->all();

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
            $userId = (int) $id;
            $c = $counts[$userId] ?? 0;
            if ($minCount === null || $c < $minCount) {
                $minCount = $c;
                $pickId   = $userId;
            }
        }

        return $pickId ?? $assignableIds[0];
    }
}