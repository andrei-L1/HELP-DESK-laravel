<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optional: Clear existing tickets (careful in production!)
        // DB::table('tickets')->truncate();
        
        $userIds = DB::table('users')->pluck('id')->toArray();

        // Get specific role users
        $agentRoleId = DB::table('roles')->where('name', 'agent')->value('id');
        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');
        
        $agentIds = $agentRoleId ? DB::table('users')->where('role_id', $agentRoleId)->pluck('id')->toArray() : [];
        if (empty($agentIds)) $agentIds = $userIds;

        $creatorIds = $userRoleId ? DB::table('users')->where('role_id', $userRoleId)->pluck('id')->toArray() : [];
        if (empty($creatorIds)) $creatorIds = $userIds;

        // If there are absolutely no users in the database, abort to prevent constraint errors
        if (empty($userIds)) {
            $this->command->error('No users found in database. Please run UserSeeder first.');
            return;
        }

        $statusIds = DB::table('ticket_statuses')->pluck('id', 'name')->toArray();
        $priorityIds = DB::table('ticket_priorities')->pluck('id', 'name')->toArray();
        // Get SLA policies for assignment
        $slaPolicies = DB::table('sla_policies')
            ->where('is_active', true)
            ->get()
            ->keyBy(function ($item) {
                return $item->priority_id . '-' . ($item->department_id ?? 'null');
            });

        // Default fallbacks if tables are empty
        $defaultStatusId = $statusIds['Open'] ?? DB::table('ticket_statuses')->insertGetId(['name' => 'Open']);
        $defaultPriorityId = $priorityIds['Medium'] ?? DB::table('ticket_priorities')->insertGetId(['name' => 'Medium']);

        $subjects = [
            'Unable to login to account',
            'Payment not reflecting after transaction',
            'Website is loading very slowly',
            'Error when uploading profile picture',
            'Request for password reset not received',
            'Feature suggestion: Dark mode',
            'Bug in mobile responsive design',
            'Invoice PDF download failing',
            'Account suspension appeal',
            'Integration with third-party API failing',
            'Refund request for order #12345',
            'Two-factor authentication setup issues',
            'Dashboard data not updating in real-time',
            'Need assistance with subscription cancellation',
            'Report inappropriate content',
        ];

        $departments = DB::table('departments')->get();

        foreach ($departments as $department) {
            for ($i = 1; $i <= 10; $i++) {
                $createdAt = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));

                $statusName = fake()->randomElement(['Open', 'Pending', 'Resolved', 'Closed']);
                $priorityName = fake()->randomElement(['Low', 'Medium', 'High', 'Urgent']);

                $statusId = $statusIds[$statusName] ?? $defaultStatusId;
                $priorityId = $priorityIds[$priorityName] ?? $defaultPriorityId;

                // Build a per-department agent cache to avoid repeated queries
                // Only pick agents who are actually members of this ticket's department
                static $deptAgentCache = [];
                if (!isset($deptAgentCache[$department->id])) {
                    $deptAgentCache[$department->id] = DB::table('user_departments')
                        ->join('users', 'users.id', '=', 'user_departments.user_id')
                        ->join('roles', 'users.role_id', '=', 'roles.id')
                        ->where('user_departments.department_id', $department->id)
                        ->whereIn('roles.name', ['agent', 'manager'])
                        ->whereNull('users.deleted_at')
                        ->distinct()
                        ->pluck('users.id')
                        ->toArray();
                }
                $deptAgentIds = $deptAgentCache[$department->id];

                // 70% chance assigned to a dept agent, 30% unassigned
                $assignedTo = (rand(1, 10) <= 7 && !empty($deptAgentIds))
                    ? fake()->randomElement($deptAgentIds)
                    : null;

                // Find the appropriate SLA policy
                $slaPolicy = null;
                
                // First try: department-specific SLA
                $slaKey = $priorityId . '-' . $department->id;
                if (isset($slaPolicies[$slaKey])) {
                    $slaPolicy = $slaPolicies[$slaKey];
                } else {
                    // Second try: global SLA for this priority
                    $globalKey = $priorityId . '-null';
                    if (isset($slaPolicies[$globalKey])) {
                        $slaPolicy = $slaPolicies[$globalKey];
                    }
                }

                // Calculate due date based on SLA resolution time
                $dueAt = null;
                if ($slaPolicy && $slaPolicy->resolution_time) {
                    $dueAt = $createdAt->copy()->addMinutes((int) $slaPolicy->resolution_time);
                    
                    // If business hours only, you'd need more complex calculation here
                    // For now, we'll just use the simple addition
                }

                // Calculate response time for some tickets (30% chance of having first response)
                $firstResponseAt = null;
                if (rand(1, 10) <= 3 && $statusName !== 'Open') {
                    // Response within SLA or breached?
                    $responseHours = rand(1, 48);
                    $firstResponseAt = $createdAt->copy()->addHours($responseHours);
                }

                // Calculate resolution time for resolved/closed tickets
                $resolvedAt = null;
                $closedAt = null;
                if ($statusName === 'Resolved' || $statusName === 'Closed') {
                    $resolutionHours = rand(2, 72);
                    $resolvedAt = $createdAt->copy()->addHours($resolutionHours);
                    
                    if ($statusName === 'Closed') {
                        $closedAt = $resolvedAt->copy()->addHours(rand(1, 24));
                    }
                }

                // Ticket number: something like TKT-TS-2025-0001
                $year = $createdAt->year;
                $uniqueSuffix = Str::random(4);
                $ticketNumber = 'TKT-' . $department->short_code . '-' . $year . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . strtoupper($uniqueSuffix);

                DB::table('tickets')->insert([
                    'ticket_number'      => $ticketNumber,
                    'subject'            => fake()->randomElement($subjects) . ' #' . $i,
                    'description'        => fake()->paragraphs(3, true),
                    'status_id'          => $statusId,
                    'priority_id'        => $priorityId,
                    'sla_policy_id'      => $slaPolicy?->id, // ← ADD THIS LINE
                    'department_id'      => $department->id,
                    'created_by'         => !empty($creatorIds) ? fake()->randomElement($creatorIds) : null,
                    'assigned_to'        => $assignedTo,
                    'first_response_at'  => $firstResponseAt,
                    'resolved_at'        => $resolvedAt,
                    'closed_at'          => $closedAt,
                    'due_at'             => $dueAt,
                    'created_at'         => $createdAt,
                    'updated_at'         => Carbon::now(),
                ]);
            }
        }

        // Create some tickets WITHOUT department (orphaned) to test global SLA
        if (!empty($departments) && !empty($userIds)) {
            for ($i = 1; $i <= 5; $i++) {
                $createdAt = Carbon::now()->subDays(rand(0, 30));
                $priorityName = fake()->randomElement(['Low', 'Medium', 'High', 'Urgent']);
                $priorityId = $priorityIds[$priorityName] ?? $defaultPriorityId;
                
                // Find global SLA for this priority
                $globalKey = $priorityId . '-null';
                $slaPolicy = $slaPolicies[$globalKey] ?? null;
                
                $dueAt = $slaPolicy && $slaPolicy->resolution_time 
                    ? $createdAt->copy()->addMinutes((int) $slaPolicy->resolution_time)
                    : null;

                DB::table('tickets')->insert([
                    'ticket_number'      => 'TKT-GLB-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'subject'            => 'Global ticket without department #' . $i,
                    'description'        => fake()->paragraphs(2, true),
                    'status_id'          => $statusIds['Open'] ?? $defaultStatusId,
                    'priority_id'        => $priorityId,
                    'sla_policy_id'      => $slaPolicy?->id, // ← ADD THIS LINE
                    'department_id'      => null, // No department
                    'created_by'         => !empty($creatorIds) ? fake()->randomElement($creatorIds) : null,
                    'assigned_to'        => null,
                    'due_at'             => $dueAt,
                    'created_at'         => $createdAt,
                    'updated_at'         => Carbon::now(),
                ]);
            }
        }

        $totalTickets = ($departments->count() * 10) + 5; // Department tickets + global tickets
        $this->command->info('🎫 Seeded ' . $totalTickets . ' support tickets with SLA policies successfully!');
    }
}