<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketPrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            [
                'name'      => 'Low',
                'level'     => 1,
                'color_hex' => '#10B981',   // green
                'sort_order' => 40,
            ],
            [
                'name'      => 'Medium',
                'level'     => 2,
                'color_hex' => '#F59E0B',   // amber/yellow
                'sort_order' => 30,
            ],
            [
                'name'      => 'High',
                'level'     => 3,
                'color_hex' => '#F97316',   // orange
                'sort_order' => 20,
            ],
            [
                'name'      => 'Urgent',
                'level'     => 4,
                'color_hex' => '#EF4444',   // red
                'sort_order' => 10,
            ],
        ];

        foreach ($priorities as $priority) {
            DB::table('ticket_priorities')->updateOrInsert(
                ['name' => $priority['name']],
                [
                    'level'       => $priority['level'],
                    'color_hex'   => $priority['color_hex'],
                    'sort_order'  => $priority['sort_order'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        $this->command->info('Priorities seeded: Low, Medium, High, Urgent');
    }
}