<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'technical', 'title' => 'Technical Support', 'sort_order' => 10],
            ['name' => 'billing', 'title' => 'Billing & Payments', 'sort_order' => 20],
            ['name' => 'access', 'title' => 'Access & Login', 'sort_order' => 30],
            ['name' => 'feature', 'title' => 'Feature Request', 'sort_order' => 40],
            ['name' => 'bug', 'title' => 'Bug Report', 'sort_order' => 50],
            ['name' => 'other', 'title' => 'Other', 'sort_order' => 100],
        ];

        foreach ($categories as $cat) {
            DB::table('ticket_categories')->updateOrInsert(
                ['name' => $cat['name']],
                array_merge($cat, [
                    'is_active'  => true,
                    'created_at' => now(),
                ])
            );
        }

        $this->command->info('Ticket categories seeded.');
    }
}
