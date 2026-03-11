<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ManagerReportController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get the departments managed by this manager
        $managedDepartments = DB::table('departments')
            ->where('manager_id', $user->id)
            ->whereNull('deleted_at')
            ->pluck('id');
        
        // Also include departments where the user is a member (in case they manage through assignment)
        $userDepartments = $user->departments()->pluck('departments.id');
        
        // Combine both - a manager should see all departments they manage OR are members of
        $allAccessibleDepartments = $managedDepartments->merge($userDepartments)->unique();
        
        if ($allAccessibleDepartments->isEmpty()) {
            // If no departments found, return empty data
            return Inertia::render('Manager/Reports/Index', [
                'reports' => [],
                'summary' => [
                    'total_tickets' => 0,
                    'resolved_tickets' => 0,
                    'resolution_rate' => 0,
                    'avg_response_time' => 0,
                    'avg_resolution_time' => 0
                ],
                'filters' => [
                    'start_date' => now()->subDays(30)->format('Y-m-d'),
                    'end_date' => now()->format('Y-m-d'),
                    'type' => 'overview',
                    'department_id' => null,
                ],
                'departments' => [],
                'reportTypes' => [
                    ['value' => 'overview', 'label' => 'Overview'],
                    ['value' => 'volume', 'label' => 'Ticket Volume'],
                    ['value' => 'performance', 'label' => 'Team Performance'],
                    ['value' => 'agent', 'label' => 'Agent Performance'],
                    ['value' => 'resolution', 'label' => 'Resolution Time'],
                ],
            ]);
        }
        
        // Get date range from request or default to last 30 days
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Get report type
        $reportType = $request->get('type', 'overview');
        
        // Get department filter (must be within accessible departments)
        $departmentId = $request->get('department_id');
        if ($departmentId && !in_array($departmentId, $allAccessibleDepartments->toArray())) {
            $departmentId = null; // Reset if not accessible
        }
        
        $reports = [];
        
        switch ($reportType) {
            case 'volume':
                $reports = $this->getVolumeReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                break;
            case 'performance':
                $reports = $this->getPerformanceReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                break;
            case 'resolution':
                $reports = $this->getResolutionReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                break;
            case 'agent':
                $reports = $this->getAgentPerformanceReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                break;
            default:
                $reports = $this->getOverviewReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
        }
        
        // Get summary stats for the period
        $summary = $this->getSummaryStats($allAccessibleDepartments, $startDate, $endDate, $departmentId);
        
        // Get available departments for filter (only the ones this manager has access to)
        $departments = DB::table('departments')
            ->whereIn('id', $allAccessibleDepartments)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'short_code']);
        
        return Inertia::render('Manager/Reports/Index', [
            'reports' => $reports,
            'summary' => $summary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'type' => $reportType,
                'department_id' => $departmentId,
            ],
            'departments' => $departments,
            'reportTypes' => [
                ['value' => 'overview', 'label' => 'Overview'],
                ['value' => 'volume', 'label' => 'Ticket Volume'],
                ['value' => 'performance', 'label' => 'Team Performance'],
                ['value' => 'agent', 'label' => 'Agent Performance'],
                ['value' => 'resolution', 'label' => 'Resolution Time'],
            ],
        ]);
    }
    
    /**
     * Get summary statistics for the entire department(s)
     */
    private function getSummaryStats($departments, $startDate, $endDate, $departmentId = null)
    {
        $query = DB::table('tickets')
            ->whereIn('department_id', $departments)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        
        $totalTickets = (clone $query)->count();
        
        // Get status IDs for resolved/closed
        $statusIds = DB::table('ticket_statuses')
            ->whereIn('name', ['Resolved', 'Closed'])
            ->pluck('id');
        
        $resolvedTickets = (clone $query)
            ->whereIn('status_id', $statusIds)
            ->count();
        
        // Calculate average response time from first_response_at
        $avgResponseTime = (clone $query)
            ->whereNotNull('first_response_at')
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, first_response_at)'));
        
        // If first_response_at doesn't exist, calculate from activity logs
        if (!$avgResponseTime) {
            $avgResponseTime = DB::table('ticket_activity_logs as tal')
                ->join('tickets as t', 't.id', '=', 'tal.ticket_id')
                ->whereIn('t.department_id', $departments)
                ->whereBetween('t.created_at', [$startDate, $endDate . ' 23:59:59'])
                ->when($departmentId, function($q) use ($departmentId) {
                    return $q->where('t.department_id', $departmentId);
                })
                ->where('tal.action', 'status_changed')
                ->where('tal.new_value', 'IN', function($q) {
                    $q->select('name')->from('ticket_statuses')
                      ->whereIn('name', ['In Progress', 'Assigned']);
                })
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, t.created_at, tal.created_at)'));
        }
        
        $avgResolutionTime = (clone $query)
            ->whereNotNull('resolved_at')
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at)'));
        
        return [
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 2) : 0,
            'avg_response_time' => round($avgResponseTime ?? 0, 1),
            'avg_resolution_time' => round($avgResolutionTime ?? 0, 1),
        ];
    }
    
    /**
     * Get overview report data for the department(s)
     */
    private function getOverviewReport($departments, $startDate, $endDate, $departmentId = null)
    {
        // Get status IDs for resolved/closed
        $resolvedStatusIds = DB::table('ticket_statuses')
            ->whereIn('name', ['Resolved', 'Closed'])
            ->pluck('id');
        
        // Daily ticket volume
        $dailyVolume = DB::table('tickets')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status_id IN (' . $resolvedStatusIds->implode(',') . ') THEN 1 ELSE 0 END) as resolved')
            )
            ->whereIn('department_id', $departments)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        
        // Status distribution
        $statusDistribution = DB::table('tickets')
            ->select('ticket_statuses.name as status', DB::raw('COUNT(*) as count'))
            ->join('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->whereIn('tickets.department_id', $departments)
            ->whereBetween('tickets.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('tickets.department_id', $departmentId);
            })
            ->groupBy('ticket_statuses.name', 'ticket_statuses.sort_order')
            ->orderBy('ticket_statuses.sort_order')
            ->get();
        
        // Priority distribution
        $priorityDistribution = DB::table('tickets')
            ->select('ticket_priorities.name as priority', DB::raw('COUNT(*) as count'))
            ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->whereIn('tickets.department_id', $departments)
            ->whereBetween('tickets.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('tickets.department_id', $departmentId);
            })
            ->groupBy('ticket_priorities.name', 'ticket_priorities.sort_order')
            ->orderBy('ticket_priorities.sort_order')
            ->get();
        
        // Department breakdown (only if viewing all departments)
        $departmentBreakdown = [];
        if (!$departmentId) {
            $departmentBreakdown = DB::table('tickets')
                ->select(
                    'departments.name as department',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN tickets.status_id IN (' . $resolvedStatusIds->implode(',') . ') THEN 1 ELSE 0 END) as resolved')
                )
                ->join('departments', 'tickets.department_id', '=', 'departments.id')
                ->whereIn('tickets.department_id', $departments)
                ->whereBetween('tickets.created_at', [$startDate, $endDate . ' 23:59:59'])
                ->groupBy('departments.name')
                ->get();
        }
        
        return [
            'daily_volume' => $dailyVolume,
            'status_distribution' => $statusDistribution,
            'priority_distribution' => $priorityDistribution,
            'department_breakdown' => $departmentBreakdown,
        ];
    }
    
    /**
     * Get volume report data for the department(s)
     */
    private function getVolumeReport($departments, $startDate, $endDate, $departmentId = null)
    {
        // Hourly volume (for heat map)
        $hourlyVolume = DB::table('tickets')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                DB::raw('COUNT(*) as count')
            )
            ->whereIn('department_id', $departments)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->groupBy(DB::raw('HOUR(created_at)'), DB::raw('DAYOFWEEK(created_at)'))
            ->get();
        
        // Weekly trend
        $weeklyTrend = DB::table('tickets')
            ->select(
                DB::raw('YEARWEEK(created_at) as week'),
                DB::raw('MIN(DATE(created_at)) as week_start'),
                DB::raw('COUNT(*) as total')
            )
            ->whereIn('department_id', $departments)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->groupBy(DB::raw('YEARWEEK(created_at)'))
            ->orderBy('week')
            ->get();
        
        // Peak hours
        $peakHours = DB::table('tickets')
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->whereIn('department_id', $departments)
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();
        
        return [
            'hourly_volume' => $hourlyVolume,
            'weekly_trend' => $weeklyTrend,
            'peak_hours' => $peakHours,
        ];
    }
    
    /**
     * Get performance report data for the department(s)
     */
    private function getPerformanceReport($departments, $startDate, $endDate, $departmentId = null)
    {
        // Response times by department
        $responseTimes = DB::table('tickets')
            ->select(
                'departments.name as department',
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.first_response_at)) as avg_response_hours'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.resolved_at)) as avg_resolution_hours'),
                DB::raw('COUNT(*) as ticket_count')
            )
            ->join('departments', 'tickets.department_id', '=', 'departments.id')
            ->whereIn('tickets.department_id', $departments)
            ->whereNotNull('tickets.first_response_at')
            ->whereBetween('tickets.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('tickets.department_id', $departmentId);
            })
            ->groupBy('departments.name')
            ->get();
        
        // If no first_response_at data, calculate from activity logs
        if ($responseTimes->isEmpty() || !$responseTimes->first()->avg_response_hours) {
            $responseTimes = DB::table('tickets')
                ->select(
                    'departments.name as department',
                    DB::raw('AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, 
                        (SELECT MIN(created_at) FROM ticket_activity_logs 
                         WHERE ticket_activity_logs.ticket_id = tickets.id 
                         AND ticket_activity_logs.action = "status_changed"
                         AND ticket_activity_logs.new_value IN ("In Progress", "Assigned")
                        ))) as avg_response_hours'),
                    DB::raw('AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.resolved_at)) as avg_resolution_hours'),
                    DB::raw('COUNT(*) as ticket_count')
                )
                ->join('departments', 'tickets.department_id', '=', 'departments.id')
                ->whereIn('tickets.department_id', $departments)
                ->whereBetween('tickets.created_at', [$startDate, $endDate . ' 23:59:59'])
                ->when($departmentId, function($q) use ($departmentId) {
                    return $q->where('tickets.department_id', $departmentId);
                })
                ->groupBy('departments.name')
                ->get();
        }
        
        // SLA compliance (based on priority deadlines)
        $slaCompliance = DB::table('tickets')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE 
                    WHEN ticket_priorities.name = "Urgent" AND TIMESTAMPDIFF(HOUR, tickets.created_at, COALESCE(tickets.resolved_at, NOW())) <= 4 THEN 1
                    WHEN ticket_priorities.name = "High" AND TIMESTAMPDIFF(HOUR, tickets.created_at, COALESCE(tickets.resolved_at, NOW())) <= 8 THEN 1
                    WHEN ticket_priorities.name = "Medium" AND TIMESTAMPDIFF(HOUR, tickets.created_at, COALESCE(tickets.resolved_at, NOW())) <= 24 THEN 1
                    WHEN ticket_priorities.name = "Low" AND TIMESTAMPDIFF(HOUR, tickets.created_at, COALESCE(tickets.resolved_at, NOW())) <= 48 THEN 1
                    ELSE 0 END) as within_sla')
            )
            ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->whereIn('tickets.department_id', $departments)
            ->whereBetween('tickets.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('tickets.department_id', $departmentId);
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        
        return [
            'response_times' => $responseTimes,
            'sla_compliance' => $slaCompliance,
        ];
    }
    
    /**
     * Get agent performance report for all agents in the department(s)
     */
    private function getAgentPerformanceReport($departments, $startDate, $endDate, $departmentId = null)
    {
        // Get status IDs for resolved/closed
        $resolvedStatusIds = DB::table('ticket_statuses')
            ->whereIn('name', ['Resolved', 'Closed'])
            ->pluck('id');
        
        // Get all agents who are members of these departments
        $agentIds = DB::table('user_departments')
            ->whereIn('department_id', $departments)
            ->pluck('user_id');
        
        // If a specific department is selected, filter agents by that department
        if ($departmentId) {
            $agentIds = DB::table('user_departments')
                ->where('department_id', $departmentId)
                ->pluck('user_id');
        }
        
        $agentPerformance = DB::table('users')
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                DB::raw('COUNT(DISTINCT tickets.id) as assigned_tickets'),
                DB::raw('SUM(CASE WHEN tickets.status_id IN (' . $resolvedStatusIds->implode(',') . ') THEN 1 ELSE 0 END) as resolved_tickets'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.first_response_at)) as avg_response_time'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, tickets.created_at, tickets.resolved_at)) as avg_resolution_time')
            )
            ->leftJoin('tickets', function($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'tickets.assigned_to')
                     ->whereBetween('tickets.created_at', [$startDate, $endDate . ' 23:59:59']);
            })
            ->whereIn('users.id', $agentIds)
            ->whereIn('tickets.department_id', $departments)
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('tickets.department_id', $departmentId);
            })
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email')
            ->orderBy('resolved_tickets', 'desc')
            ->get();
        
        return $agentPerformance;
    }
    
    /**
     * Get resolution time report for the department(s)
     */
    private function getResolutionReport($departments, $startDate, $endDate, $departmentId = null)
    {
        // Resolution time distribution
        $resolutionDistribution = DB::table('tickets')
            ->select(
                DB::raw('
                    CASE 
                        WHEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) <= 1 THEN "Under 1 hour"
                        WHEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) <= 4 THEN "1-4 hours"
                        WHEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) <= 24 THEN "4-24 hours"
                        WHEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) <= 48 THEN "24-48 hours"
                        ELSE "Over 48 hours"
                    END as resolution_time_range
                '),
                DB::raw('COUNT(*) as count')
            )
            ->whereIn('department_id', $departments)
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->groupBy('resolution_time_range')
            ->orderBy(DB::raw('
                CASE resolution_time_range
                    WHEN "Under 1 hour" THEN 1
                    WHEN "1-4 hours" THEN 2
                    WHEN "4-24 hours" THEN 3
                    WHEN "24-48 hours" THEN 4
                    ELSE 5
                END
            '))
            ->get();
        
        // Monthly resolution trends
        $monthlyTrends = DB::table('tickets')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_resolution_hours'),
                DB::raw('COUNT(*) as resolved_count')
            )
            ->whereIn('department_id', $departments)
            ->whereNotNull('resolved_at')
            ->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->when($departmentId, function($q) use ($departmentId) {
                return $q->where('department_id', $departmentId);
            })
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->get();
        
        return [
            'resolution_distribution' => $resolutionDistribution,
            'monthly_trends' => $monthlyTrends,
        ];
    }
    
    /**
     * Export report data
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Get accessible departments
        $managedDepartments = DB::table('departments')
            ->where('manager_id', $user->id)
            ->whereNull('deleted_at')
            ->pluck('id');
        
        $userDepartments = $user->departments()->pluck('departments.id');
        $allAccessibleDepartments = $managedDepartments->merge($userDepartments)->unique();
        
        if ($allAccessibleDepartments->isEmpty()) {
            return response()->json(['error' => 'No departments found'], 404);
        }
        
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $reportType = $request->get('type', 'overview');
        $format = $request->get('format', 'csv');
        $departmentId = $request->get('department_id');
        
        // Validate department filter
        if ($departmentId && !in_array($departmentId, $allAccessibleDepartments->toArray())) {
            $departmentId = null;
        }
        
        // Generate report data
        $data = [];
        $filename = "department_report_{$reportType}_{$startDate}_to_{$endDate}";
        
        switch ($reportType) {
            case 'overview':
                $data = $this->getOverviewReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                $filename = "department_overview_{$startDate}_to_{$endDate}";
                break;
            case 'volume':
                $data = $this->getVolumeReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                $filename = "department_volume_{$startDate}_to_{$endDate}";
                break;
            case 'performance':
                $data = $this->getPerformanceReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                $filename = "department_performance_{$startDate}_to_{$endDate}";
                break;
            case 'agent':
                $data = $this->getAgentPerformanceReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                $filename = "department_agent_performance_{$startDate}_to_{$endDate}";
                break;
            case 'resolution':
                $data = $this->getResolutionReport($allAccessibleDepartments, $startDate, $endDate, $departmentId);
                $filename = "department_resolution_{$startDate}_to_{$endDate}";
                break;
        }
        
        if ($format === 'csv') {
            return $this->exportToCsv($data, $filename, $reportType);
        }
        
        return $this->exportToCsv($data, $filename, $reportType);
    }
    
    /**
     * Export data to CSV
     */
    private function exportToCsv($data, $filename, $reportType)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];
        
        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            switch ($reportType) {
                case 'overview':
                    fputcsv($file, ['Date', 'Total Tickets', 'Resolved']);
                    foreach ($data['daily_volume'] ?? [] as $row) {
                        fputcsv($file, [(array) $row]);
                    }
                    break;
                    
                case 'agent':
                    fputcsv($file, ['Agent', 'Assigned', 'Resolved', 'Resolution Rate %', 'Avg Response (hrs)', 'Avg Resolution (hrs)']);
                    foreach ($data as $row) {
                        $resolutionRate = $row->assigned_tickets > 0 
                            ? round(($row->resolved_tickets / $row->assigned_tickets) * 100, 1) 
                            : 0;
                        
                        fputcsv($file, [
                            $row->first_name . ' ' . $row->last_name,
                            $row->assigned_tickets,
                            $row->resolved_tickets,
                            $resolutionRate,
                            round($row->avg_response_time ?? 0, 1),
                            round($row->avg_resolution_time ?? 0, 1),
                        ]);
                    }
                    break;
                    
                case 'performance':
                    fputcsv($file, ['Department', 'Ticket Count', 'Avg Response (hrs)', 'Avg Resolution (hrs)']);
                    foreach ($data['response_times'] ?? [] as $row) {
                        fputcsv($file, [
                            $row->department,
                            $row->ticket_count,
                            round($row->avg_response_hours ?? 0, 1),
                            round($row->avg_resolution_hours ?? 0, 1),
                        ]);
                    }
                    break;
                    
                case 'resolution':
                    fputcsv($file, ['Resolution Time Range', 'Ticket Count']);
                    foreach ($data['resolution_distribution'] ?? [] as $row) {
                        fputcsv($file, [$row->resolution_time_range, $row->count]);
                    }
                    break;
                    
                case 'volume':
                    fputcsv($file, ['Hour', 'Count']);
                    foreach ($data['peak_hours'] ?? [] as $row) {
                        fputcsv($file, [$row->hour . ':00', $row->count]);
                    }
                    break;
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}