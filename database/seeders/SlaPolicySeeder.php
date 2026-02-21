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

        $this->command->info('SLA policies seeded.');
    }
}
