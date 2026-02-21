<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Technical Support', 'short_code' => 'TS'],
            ['name' => 'Billing', 'short_code' => 'BILL'],
            ['name' => 'Sales', 'short_code' => 'SALES'],
            ['name' => 'General', 'short_code' => 'GEN'],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->updateOrInsert(
                ['short_code' => $dept['short_code']],
                array_merge($dept, [
                    'is_active'   => true,
                    'description' => null,
                    'manager_id'  => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])
            );
        }

        $this->command->info('Departments seeded.');
    }
}
