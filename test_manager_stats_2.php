<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

$out = "";
$managers = User::whereHas('role', function($q){ $q->where('name', 'manager'); })->get();

foreach($managers as $manager) {
    $out .= "Manager ID: {$manager->id} ({$manager->email})\n";
    $userDepartments = $manager->departments()->pluck('departments.id')->toArray();
    $out .= "  Departments: " . implode(',', $userDepartments) . "\n";
    
    $team = User::whereHas('role', function ($q) { $q->where('name', 'agent'); })
                ->whereHas('departments', function ($q) use ($userDepartments) {
                    $q->whereIn('departments.id', $userDepartments);
                })->get();
    
    $out .= "  Agents found: " . $team->count() . "\n";
    foreach($team as $agent) {
        $assigned = Ticket::where('assigned_to', $agent->id)
            ->whereIn('department_id', $userDepartments)
            ->whereHas('status', function ($s) {
                $s->where('name', '!=', 'Closed'); 
            })->count();
            
        $resolved = Ticket::where('assigned_to', $agent->id)
            ->whereIn('department_id', $userDepartments)
            ->whereHas('status', function ($s) {
                $s->where('name', 'Resolved'); 
            })->count();
            
        $totalForAgent = Ticket::where('assigned_to', $agent->id)->count();
            
        $out .= "    Agent {$agent->id} (Name: {$agent->first_name}): Assigned in DEPT = {$assigned}, Resolved = {$resolved}. Total = {$totalForAgent}\n";
    }
}
file_put_contents('out.txt', $out);
