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

        // Get existing IDs to reference (fallback to null if empty)
        $userIds = DB::table('users')->pluck('id')->toArray();
        $statusIds = DB::table('ticket_statuses')->pluck('id', 'name')->toArray();
        $priorityIds = DB::table('ticket_priorities')->pluck('id', 'name')->toArray();

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

        for ($i = 1; $i <= 35; $i++) {  // Create 35 tickets
            $createdAt = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));

            $statusName = fake()->randomElement(['Open', 'Pending', 'Resolved', 'Closed', 'Urgent']);
            $priorityName = fake()->randomElement(['Low', 'Medium', 'High', 'Urgent']);

            $statusId = $statusIds[$statusName] ?? $defaultStatusId;
            $priorityId = $priorityIds[$priorityName] ?? $defaultPriorityId;

            // 70% chance assigned to someone, 30% unassigned
            $assignedTo = (rand(1, 10) <= 7 && !empty($userIds))
                ? fake()->randomElement($userIds)
                : null;

            // Ticket number: something like TICKET-2025-0001
            $year = $createdAt->year;
            $ticketNumber = 'TICKET-' . $year . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            DB::table('tickets')->insert([
                'ticket_number'  => $ticketNumber,
                'subject'        => fake()->randomElement($subjects) . ' #' . $i,
                'description'    => fake()->paragraphs(3, true),  // assuming you have a description column
                'status_id'      => $statusId,
                'priority_id'    => $priorityId,
                'created_by'     => !empty($userIds) ? fake()->randomElement($userIds) : null,
                'assigned_to'    => $assignedTo,
                'created_at'     => $createdAt,
                'updated_at'     => Carbon::now(),
            ]);
        }

        $this->command->info('🎫 Seeded ' . 35 . ' support tickets successfully!');
    }
}