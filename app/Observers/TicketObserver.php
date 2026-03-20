<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketStatusChangedNotification;

class TicketObserver
{
    /**
     * Handle the Ticket "creating" event (before save).
     */
    public function creating(Ticket $ticket): void
    {
        // Auto-calculate SLA when a ticket is created
        $this->applySlaPolicy($ticket);
    }

    /**
     * Handle the Ticket "updating" event (before save).
     */
    public function updating(Ticket $ticket): void
    {
        // Recalculate SLA if priority or department changes
        if ($ticket->isDirty(['priority_id', 'department_id']) || (!$ticket->due_at && $ticket->priority_id)) {
            $this->applySlaPolicy($ticket);
        }
    }

    private function applySlaPolicy(Ticket $ticket): void
    {
        if (!$ticket->priority_id) return;

        $sla = $this->findSlaPolicy($ticket->priority_id, $ticket->department_id);
        
        if ($sla) {
            $ticket->sla_policy_id = $sla->id;
            
            // USE THE SMART SLA SERVICE
            $slaService = resolve(\App\Services\SlaService::class);
            $ticket->due_at = $slaService->calculateDeadline(now(), $sla);
        }
    }

    private function findSlaPolicy(int $priorityId, ?int $departmentId): ?\App\Models\SlaPolicy
    {
        // 1. Try Department-specific first
        if ($departmentId) {
            $policy = \App\Models\SlaPolicy::where('priority_id', $priorityId)
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->first();
            if ($policy) return $policy;
        }

        // 2. Global fallback
        return \App\Models\SlaPolicy::where('priority_id', $priorityId)
            ->whereNull('department_id')
            ->where('is_active', true)
            ->first();
    }
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        // 1. Notify the Creator (User)
        if ($ticket->creator) {
            $ticket->creator->notify(new TicketCreatedNotification($ticket));
        }

        // 2. Notify the Assignee (Agent/Manager/Admin) if already assigned
        if ($ticket->assignee && $ticket->assigned_to !== $ticket->created_by) {
            $ticket->assignee->notify(new TicketAssignedNotification($ticket));
        }
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // 1. Check if assigned_to changed
        if ($ticket->wasChanged('assigned_to') && $ticket->assigned_to) {
            if ($ticket->assignee && $ticket->assigned_to !== $ticket->created_by) {
                $ticket->assignee->notify(new TicketAssignedNotification($ticket));
            }
        }

        // 2. Check if status changed
        if ($ticket->wasChanged('status_id')) {
            if ($ticket->creator) {
                // Load status relation if not loaded
                $ticket->loadMissing('status');
                $ticket->creator->notify(new TicketStatusChangedNotification($ticket));
            }
        }

        // 3. Trigger Real-time Update via Pusher
        // We broadcast if major fields change that affect the UI (SLA, status, assignment)
        if ($ticket->wasChanged(['status_id', 'priority_id', 'assigned_to', 'due_at', 'sla_policy_id', 'is_sla_breached'])) {
            broadcast(new \App\Events\TicketUpdated($ticket));
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
