<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return Inertia::render('Agent/Tickets/Index', [
            'tickets' => $tickets,
            'currentStatus' => $statusName,
        ]);
    }

    public function create()
    {
        return Inertia::render('Agent/Tickets/Create', [
            'priorities' => \App\Models\TicketPriority::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Validation + create logic here
        // Example:
        $validated = $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string',
            'priority_id' => 'required|exists:priorities,id',
            // etc.
        ]);

        $ticket = Ticket::create([
            'subject'      => $validated['subject'],
            'description'  => $validated['description'],
            'priority_id'  => $validated['priority_id'],
            'assigned_to'  => Auth::id(),
            'status_id'    => TicketStatus::where('name', 'Open')->firstOrFail()->id,
            'created_by'   => Auth::id(),
            // customer_id, department_id, etc.
        ]);

        return redirect()->route('agent.tickets.show', $ticket)
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeAgentAccess($ticket);

        $ticket->load(['status', 'priority', 'creator', 'replies.author']);

        return Inertia::render('Agent/Tickets/Show', [
            'ticket' => $ticket,
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

        // Validate + create reply
        // Update ticket status if needed (e.g. to Pending)

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
}