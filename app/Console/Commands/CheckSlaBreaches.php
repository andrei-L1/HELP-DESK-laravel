<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:check-sla-breaches {--force : Force processing even if already alerted (not recommended)}';

    /**
     * The console command description.
     */
    protected $description = 'Scan unresolved tickets for SLA warnings and breaches.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SLA check at ' . now()->toDateTimeString());

        // We only care about tickets that aren't Resolved or Closed.
        $unresolvedStatuses = \Illuminate\Support\Facades\DB::table('ticket_statuses')
            ->whereNotIn('name', ['Resolved', 'Closed'])
            ->pluck('id');

        $tickets = \App\Models\Ticket::with(['assignee', 'creator', 'slaPolicy', 'department.manager'])
            ->whereIn('status_id', $unresolvedStatuses)
            ->whereNotNull('due_at')
            ->get();

        $this->info("Found {$tickets->count()} unresolved tickets to check.");

        foreach ($tickets as $ticket) {
            /** @var \App\Models\Ticket $ticket */
            $this->processTicket($ticket);
        }

        $this->info('SLA check completed.');
    }

    protected function processTicket(\App\Models\Ticket $ticket)
    {
        $now = now();
        $dueAt = $ticket->due_at;
        $sla = $ticket->slaPolicy;

        if (!$sla) {
            return;
        }

        // 🚨 1. BREACH CHECK (Already missed the deadline)
        if ($now->greaterThan($dueAt)) {
            if (!$ticket->is_sla_breached || $this->option('force')) {
                $this->warn("Ticket #{$ticket->ticket_number} has BREACHED SLA.");
                
                $ticket->update([
                    'is_sla_breached'    => true,
                    'sla_breach_sent_at' => $now,
                ]);

                $this->notifyBreach($ticket);
                $this->logActivity($ticket, 'sla_breached', $dueAt->toDateTimeString());
            }
            return; // No need to check warning if already breached
        }

        // ⚠️ 2. WARNING CHECK (Approaching deadline)
        $notifyBefore = (int) ($sla->notify_before ?? 30); // minutes
        $warningLimit = $dueAt->copy()->subMinutes($notifyBefore);

        if ($now->greaterThan($warningLimit)) {
            if (is_null($ticket->sla_warning_sent_at) || $this->option('force')) {
                $this->line("Ticket #{$ticket->ticket_number} is in WARNING zone.");
                
                $ticket->update([
                    'sla_warning_sent_at' => $now,
                ]);

                $this->notifyWarning($ticket);
                $this->logActivity($ticket, 'sla_warning_sent', $dueAt->toDateTimeString());
            }
        }
    }

    protected function notifyWarning(\App\Models\Ticket $ticket)
    {
        $recipients = collect();

        // Notify assignee
        if ($ticket->assignee) {
            $recipients->push($ticket->assignee);
        }

        // Notify department manager if it's high priority
        if ($ticket->priority?->name === 'Urgent' && $ticket->department?->manager) {
            $recipients->push($ticket->department->manager);
        }

        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($recipients->unique('id'), new \App\Notifications\SlaWarningNotification($ticket));
        }
    }

    protected function notifyBreach(\App\Models\Ticket $ticket)
    {
        $recipients = collect();

        // Notify assignee
        if ($ticket->assignee) {
            $recipients->push($ticket->assignee);
        }

        // Notify department manager AND all Admins (for visibility)
        if ($ticket->department?->manager) {
            $recipients->push($ticket->department->manager);
        }

        // Pull admins for critical breaches
        $admins = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();
        $recipients = $recipients->merge($admins);

        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($recipients->unique('id'), new \App\Notifications\SlaBreachNotification($ticket));
        }
    }

    protected function logActivity(\App\Models\Ticket $ticket, string $action, string $newValue)
    {
        // Fallback to first admin for system actions if no logged-in user
        $systemUserId = \Illuminate\Support\Facades\Auth::id() ?? 
                       \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'admin'))->value('id');

        \App\Models\TicketActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $systemUserId,
            'action'    => $action,
            'old_value' => null,
            'new_value' => $newValue,
        ]);
    }
}
