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
                'name'            => 'Low - 48h resolution',
                'priority_id'     => $priorityIds['Low'] ?? null,
                'response_time'   => 480,   // 8 hours
                'resolution_time' => 2880,  // 48 hours
            ],
            [
                'name'            => 'Medium - 24h resolution',
                'priority_id'     => $priorityIds['Medium'] ?? null,
                'response_time'   => 240,   // 4 hours
                'resolution_time' => 1440,  // 24 hours
            ],
            [
                'name'            => 'High - 8h resolution',
                'priority_id'     => $priorityIds['High'] ?? null,
                'response_time'   => 60,    // 1 hour
                'resolution_time' => 480,   // 8 hours
            ],
            [
                'name'            => 'Urgent - 4h resolution',
                'priority_id'     => $priorityIds['Urgent'] ?? null,
                'response_time'   => 30,   // 30 min
                'resolution_time' => 240,   // 4 hours
            ],
        ];

        // Global SLAs (apply when ticket has no department or no department-specific SLA)
        foreach ($policies as $policy) {
            if ($policy['priority_id']) {
                DB::table('sla_policies')->updateOrInsert(
                    ['priority_id' => $policy['priority_id'], 'department_id' => null],
                    array_merge($policy, [
                        'department_id' => null,
                        'is_active'     => true,
                        'created_at'    => now(),
                    ])
                );
            }
        }

        // Optional: department-specific SLAs (override global for that priority + department)
        $deptIds = DB::table('departments')->whereNull('deleted_at')->pluck('id', 'name')->all();
        if (!empty($deptIds) && isset($deptIds['Technical Support'], $priorityIds['High'])) {
            DB::table('sla_policies')->updateOrInsert(
                ['priority_id' => $priorityIds['High'], 'department_id' => $deptIds['Technical Support']],
                [
                    'name'            => 'High - Technical Support (4h)',
                    'priority_id'     => $priorityIds['High'],
                    'department_id'   => $deptIds['Technical Support'],
                    'response_time'   => 30,
                    'resolution_time' => 240, // 4 hours
                    'is_active'       => true,
                    'created_at'      => now(),
                ]
            );
        }

        $this->command->info('SLA policies seeded (global + optional department-specific).');
    }
}
