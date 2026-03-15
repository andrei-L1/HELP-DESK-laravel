<?php

use Illuminate\Support\Facades\Artisan;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

// Find a manager user
$manager = User::whereHas('role', function($q) { $q->where('name', 'manager'); })->first();

if (!$manager) {
    echo "No manager found.\n";
    exit;
}

echo "Manager ID: {$manager->id}\n";
$userDepartments = $manager->departments()->pluck('departments.id');
echo "Manager Departments: " . json_encode($userDepartments) . "\n";

$teamPerformance = User::whereHas('role', function ($q) {
        $q->where('name', 'agent'); 
    })
    ->whereHas('departments', function ($q) use ($userDepartments) {
        $q->whereIn('departments.id', $userDepartments);
    })
    ->withCount(['assignedTickets as assigned' => function ($q) use ($userDepartments) {
        $q->whereIn('department_id', $userDepartments)
          ->whereHas('status', function ($s) {
              $s->where('name', '!=', 'Closed'); 
          });
    }])
    ->get()
    ->map(function ($agent) use ($userDepartments) {
        return [
            'id' => $agent->id,
            'name' => trim("{$agent->first_name} {$agent->last_name}"),
            'assigned_count_from_query' => $agent->assigned,
            'resolved' => Ticket::where('assigned_to', $agent->id)
                ->whereIn('department_id', $userDepartments)
                ->whereHas('status', function ($s) {
                    $s->where('name', 'Resolved'); 
                })
                ->count(),
            'pending' => Ticket::where('assigned_to', $agent->id)
                ->whereIn('department_id', $userDepartments)
                ->whereHas('status', function ($s) {
                    $s->where('name', 'Pending'); 
                })
                ->count(),
        ];
    });

echo "Team Performance: \n" . json_encode($teamPerformance, JSON_PRETTY_PRINT) . "\n";
