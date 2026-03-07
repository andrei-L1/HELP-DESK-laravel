<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlaPolicySeeder extends Seeder
{
    /**
     * response_time and resolution_time in minutes.
     */
    public function run(): void
    {
        $priorityIds = DB::table('ticket_priorities')->pluck('id', 'name')->all();
        if (empty($priorityIds)) {
            $this->command->warn('Run TicketPrioritySeeder first.');
            return;
        }

        $policies = [
            [
                'name'               => 'Low - 48h resolution',
                'description'        => 'Standard SLA for low priority tickets. Response within 8 hours, resolution within 48 hours.',
                'priority_id'        => $priorityIds['Low'] ?? null,
                'response_time'      => 480,   // 8 hours
                'resolution_time'    => 2880,  // 48 hours
                'escalate_after'     => 1440,  // Escalate after 24 hours if not resolved
                'notify_before'      => 60,    // Notify 1 hour before deadline
                'notify_escalation'  => true,
                'business_hours_only' => true,
            ],
            [
                'name'               => 'Medium - 24h resolution',
                'description'        => 'Standard SLA for medium priority tickets. Response within 4 hours, resolution within 24 hours.',
                'priority_id'        => $priorityIds['Medium'] ?? null,
                'response_time'      => 240,   // 4 hours
                'resolution_time'    => 1440,  // 24 hours
                'escalate_after'     => 720,   // Escalate after 12 hours if not resolved
                'notify_before'      => 30,    // Notify 30 minutes before deadline
                'notify_escalation'  => true,
                'business_hours_only' => true,
            ],
            [
                'name'               => 'High - 8h resolution',
                'description'        => 'High priority SLA. Response within 1 hour, resolution within 8 hours.',
                'priority_id'        => $priorityIds['High'] ?? null,
                'response_time'      => 60,    // 1 hour
                'resolution_time'    => 480,   // 8 hours
                'escalate_after'     => 240,   // Escalate after 4 hours if not resolved
                'notify_before'      => 15,    // Notify 15 minutes before deadline
                'notify_escalation'  => true,
                'business_hours_only' => false, // 24/7 for high priority
            ],
            [
                'name'               => 'Urgent - 4h resolution',
                'description'        => 'Urgent priority SLA. Response within 30 minutes, resolution within 4 hours.',
                'priority_id'        => $priorityIds['Urgent'] ?? null,
                'response_time'      => 30,    // 30 min
                'resolution_time'    => 240,   // 4 hours
                'escalate_after'     => 120,   // Escalate after 2 hours if not resolved
                'notify_before'      => 10,    // Notify 10 minutes before deadline
                'notify_escalation'  => true,
                'business_hours_only' => false, // 24/7 for urgent
            ],
        ];

        // Global SLAs (apply when ticket has no department or no department-specific SLA)
        foreach ($policies as $policy) {
            if ($policy['priority_id']) {
                DB::table('sla_policies')->updateOrInsert(
                    [
                        'priority_id' => $policy['priority_id'], 
                        'department_id' => null
                    ],
                    array_merge($policy, [
                        'department_id' => null,
                        'is_active'     => true,
                        'calendar_id'   => null,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ])
                );
            }
        }

        // Optional: department-specific SLAs (override global for that priority + department)
        $deptIds = DB::table('departments')->whereNull('deleted_at')->pluck('id', 'name')->all();
        
        if (!empty($deptIds) && isset($deptIds['Technical Support'], $priorityIds['High'])) {
            DB::table('sla_policies')->updateOrInsert(
                [
                    'priority_id' => $priorityIds['High'], 
                    'department_id' => $deptIds['Technical Support']
                ],
                [
                    'name'               => 'High - Technical Support (4h)',
                    'description'        => 'Department-specific SLA for Technical Support. Faster resolution for high priority tickets.',
                    'priority_id'        => $priorityIds['High'],
                    'department_id'      => $deptIds['Technical Support'],
                    'response_time'      => 30,    // 30 minutes
                    'resolution_time'    => 240,   // 4 hours
                    'escalate_after'     => 120,   // 2 hours
                    'notify_before'      => 15,    // 15 minutes
                    'notify_escalation'  => true,
                    'is_active'          => true,
                    'business_hours_only' => false, // 24/7 for critical department tickets
                    'calendar_id'        => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]
            );
        }

        // Add another department-specific SLA if Billing department exists
        if (!empty($deptIds) && isset($deptIds['Billing'], $priorityIds['Medium'])) {
            DB::table('sla_policies')->updateOrInsert(
                [
                    'priority_id' => $priorityIds['Medium'], 
                    'department_id' => $deptIds['Billing']
                ],
                [
                    'name'               => 'Medium - Billing (12h)',
                    'description'        => 'Billing department SLA with extended resolution time due to verification processes.',
                    'priority_id'        => $priorityIds['Medium'],
                    'department_id'      => $deptIds['Billing'],
                    'response_time'      => 120,   // 2 hours
                    'resolution_time'    => 720,   // 12 hours
                    'escalate_after'     => 360,   // 6 hours
                    'notify_before'      => 30,    // 30 minutes
                    'notify_escalation'  => true,
                    'is_active'          => true,
                    'business_hours_only' => true,  // Business hours only for billing
                    'calendar_id'        => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]
            );
        }

        // Add an inactive policy example (for testing)
        if (isset($priorityIds['Low'])) {
            DB::table('sla_policies')->updateOrInsert(
                [
                    'priority_id' => $priorityIds['Low'], 
                    'department_id' => null,
                    'name' => 'Low - Legacy Policy (Inactive)'
                ],
                [
                    'name'               => 'Low - Legacy Policy (Inactive)',
                    'description'        => 'This is an old SLA policy kept for historical reference.',
                    'priority_id'        => $priorityIds['Low'],
                    'department_id'      => null,
                    'response_time'      => 600,   // 10 hours
                    'resolution_time'    => 4320,  // 72 hours
                    'escalate_after'     => 2160,  // 36 hours
                    'notify_before'      => 120,   // 2 hours
                    'notify_escalation'  => false,
                    'is_active'          => false, // Inactive
                    'business_hours_only' => true,
                    'calendar_id'        => null,
                    'created_at'         => now()->subDays(30),
                    'updated_at'         => now()->subDays(30),
                ]
            );
        }

        $this->command->info('SLA policies seeded with all fields (description, escalation, notifications, etc.).');
    }
}