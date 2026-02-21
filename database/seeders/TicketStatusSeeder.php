<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Open',     'title' => 'Open Ticket',     'color_hex' => '#3B82F6'],   // blue
            ['name' => 'Pending',  'title' => 'Pending Response', 'color_hex' => '#F59E0B'],   // amber/yellow
            ['name' => 'Resolved', 'title' => 'Resolved',         'color_hex' => '#10B981'],   // green
            ['name' => 'Closed',   'title' => 'Closed',           'color_hex' => '#6B7280'],   // gray
            ['name' => 'Urgent',   'title' => 'Urgent / Escalated','color_hex' => '#EF4444'],   // red
        ];

        foreach ($statuses as $status) {
            DB::table('ticket_statuses')->updateOrInsert(
                ['name' => $status['name']],
                [
                    'title'       => $status['title'],
                    'color_hex'   => $status['color_hex'],
                    'is_active'   => true,
                    'is_closed'   => ($status['name'] === 'Closed'),
                    'is_resolved' => ($status['name'] === 'Resolved'),
                    'sort_order'  => match ($status['name']) {
                        'Urgent'   => 10,
                        'Open'     => 20,
                        'Pending'  => 30,
                        'Resolved' => 40,
                        'Closed'   => 50,
                        default    => 100,
                    },
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }
}