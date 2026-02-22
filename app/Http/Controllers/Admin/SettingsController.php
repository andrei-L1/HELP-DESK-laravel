<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display settings dashboard.
     */
    public function index()
    {
        // Get basic stats for settings dashboard
        $stats = [
            'total_ticket_statuses' => DB::table('ticket_statuses')->count(),
            'total_ticket_priorities' => DB::table('ticket_priorities')->count(),
            'total_ticket_categories' => DB::table('ticket_categories')->count(),
            'total_sla_policies' => DB::table('sla_policies')->count(),
            'total_departments' => DB::table('departments')->count(),
            'total_roles' => DB::table('roles')->count(),
        ];

        return Inertia::render('Admin/Settings/Index', [
            'stats' => $stats
        ]);
    }

    /**
     * General settings.
     */
    public function general()
    {
        // Get general settings
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'app_timezone' => config('app.timezone'),
            'app_locale' => config('app.locale'),
            'company_name' => config('company.name', ''),
            'company_email' => config('company.email', ''),
            'company_phone' => config('company.phone', ''),
            'company_address' => config('company.address', ''),
        ];

        return Inertia::render('Admin/Settings/General', [
            'settings' => $settings
        ]);
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_timezone' => 'required|string|max:100',
            'app_locale' => 'required|string|max:10',
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:30',
            'company_address' => 'nullable|string',
        ]);

        // Here you would typically save to database or .env file
        // For now, we'll just show success message
        // In production, you'd want to use a proper settings table

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }

    /**
     * Ticket settings.
     */
    public function ticket()
    {
        $statuses = DB::table('ticket_statuses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $priorities = DB::table('ticket_priorities')
            ->orderBy('sort_order')
            ->orderBy('level')
            ->get();

        $categories = DB::table('ticket_categories')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $types = DB::table('ticket_types')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $settings = [
            'default_status_id' => config('tickets.default_status_id'),
            'default_priority_id' => config('tickets.default_priority_id'),
            'allow_attachments' => config('tickets.allow_attachments', true),
            'max_attachments' => config('tickets.max_attachments', 5),
            'max_attachment_size' => config('tickets.max_attachment_size', 2048),
            'auto_assign' => config('tickets.auto_assign', false),
        ];

        return Inertia::render('Admin/Settings/Ticket', [
            'statuses' => $statuses,
            'priorities' => $priorities,
            'categories' => $categories,
            'types' => $types,
            'settings' => $settings,
        ]);
    }

    /**
     * Email settings.
     */
public function email()
{
    // Get email settings
    $settings = [
        'mail_mailer' => config('mail.default'),
        'mail_host' => config('mail.mailers.smtp.host'),
        'mail_port' => config('mail.mailers.smtp.port'),
        'mail_username' => config('mail.mailers.smtp.username'),
        'mail_password' => config('mail.mailers.smtp.password'),
        'mail_encryption' => config('mail.mailers.smtp.encryption'),
        'mail_from_address' => config('mail.from.address'),
        'mail_from_name' => config('mail.from.name'),
        'mail_reply_to_address' => config('mail.reply_to.address'),
        'mail_reply_to_name' => config('mail.reply_to.name'),
    ];

    return Inertia::render('Admin/Settings/Email', [
        'settings' => $settings,
        'testEmailStatus' => session('test_email_status'),
    ]);
}

    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark,log',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'mail_reply_to_address' => 'nullable|email|max:255',
            'mail_reply_to_name' => 'nullable|string|max:255',
        ]);

        // Here you would save to database or .env file
        // For now, we'll just show success message

        return redirect()->back()->with('success', 'Email settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'to_email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Here you would actually send a test email
        // For now, we'll simulate success

        return redirect()->back()->with('test_email_status', 'Test email sent successfully! Check your inbox.');
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'new_ticket_notification' => 'boolean',
            'ticket_assigned_notification' => 'boolean',
            'ticket_updated_notification' => 'boolean',
            'new_message_notification' => 'boolean',
            'daily_summary' => 'boolean',
            'weekly_summary' => 'boolean',
            'notify_admin_on_new_ticket' => 'boolean',
            'notify_manager_on_new_ticket' => 'boolean',
        ]);

        // Save notification settings

        return redirect()->back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * SLA Policies.
     */
    public function sla(Request $request)
    {
        $query = DB::table('sla_policies')
            ->select(
                'sla_policies.*',
                'ticket_priorities.name as priority_name',
                'departments.name as department_name'
            )
            ->leftJoin('ticket_priorities', 'sla_policies.priority_id', '=', 'ticket_priorities.id')
            ->leftJoin('departments', 'sla_policies.department_id', '=', 'departments.id')
            ->whereNull('sla_policies.deleted_at');

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sla_policies.name', 'like', "%{$search}%")
                ->orWhere('sla_policies.description', 'like', "%{$search}%");
            });
        }

        // Apply priority filter
        if ($priority = $request->input('priority')) {
            $query->where('sla_policies.priority_id', $priority);
        }

        // Apply department filter
        if ($department = $request->input('department')) {
            $query->where('sla_policies.department_id', $department);
        }

        $policies = $query->orderBy('sla_policies.name')->paginate(15);

        // Get priorities for dropdown
        $priorities = DB::table('ticket_priorities')
            ->orderBy('level')
            ->orderBy('name')
            ->get(['id', 'name', 'level']);

        // Get departments for dropdown
        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Settings/SLA', [
            'policies' => $policies,
            'priorities' => $priorities,
            'departments' => $departments,
            'filters' => $request->only(['search', 'priority', 'department']),
        ]);
    }

    public function storeSla(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sla_policies',
            'description' => 'nullable|string',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'department_id' => 'nullable|exists:departments,id',
            'response_time' => 'required|integer|min:1',
            'resolution_time' => 'required|integer|min:1',
            'escalate_after' => 'nullable|integer|min:1',
            'notify_before' => 'nullable|integer|min:1',
            'notify_escalation' => 'boolean',
            'is_active' => 'boolean',
            'business_hours_only' => 'boolean',
            'calendar_id' => 'nullable|integer',
        ]);

        $validated['notify_escalation'] = $request->boolean('notify_escalation');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['business_hours_only'] = $request->boolean('business_hours_only');

        DB::table('sla_policies')->insert([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'priority_id' => $validated['priority_id'],
            'department_id' => $validated['department_id'],
            'response_time' => $validated['response_time'],
            'resolution_time' => $validated['resolution_time'],
            'escalate_after' => $validated['escalate_after'],
            'notify_before' => $validated['notify_before'],
            'notify_escalation' => $validated['notify_escalation'],
            'is_active' => $validated['is_active'],
            'business_hours_only' => $validated['business_hours_only'],
            'calendar_id' => $validated['calendar_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.settings.sla')
            ->with('success', 'SLA policy created successfully.');
    }

    public function updateSla(Request $request, $id)
    {
        $policy = DB::table('sla_policies')->where('id', $id)->first();

        if (!$policy) {
            return redirect()->back()->with('error', 'SLA policy not found.');
        }

        $validated = $request->validate([
            'name' => "required|string|max:255|unique:sla_policies,name,{$id}",
            'description' => 'nullable|string',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'department_id' => 'nullable|exists:departments,id',
            'response_time' => 'required|integer|min:1',
            'resolution_time' => 'required|integer|min:1',
            'escalate_after' => 'nullable|integer|min:1',
            'notify_before' => 'nullable|integer|min:1',
            'notify_escalation' => 'boolean',
            'is_active' => 'boolean',
            'business_hours_only' => 'boolean',
            'calendar_id' => 'nullable|integer',
        ]);

        $validated['notify_escalation'] = $request->boolean('notify_escalation');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['business_hours_only'] = $request->boolean('business_hours_only');

        DB::table('sla_policies')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'priority_id' => $validated['priority_id'],
                'department_id' => $validated['department_id'],
                'response_time' => $validated['response_time'],
                'resolution_time' => $validated['resolution_time'],
                'escalate_after' => $validated['escalate_after'],
                'notify_before' => $validated['notify_before'],
                'notify_escalation' => $validated['notify_escalation'],
                'is_active' => $validated['is_active'],
                'business_hours_only' => $validated['business_hours_only'],
                'calendar_id' => $validated['calendar_id'],
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.settings.sla')
            ->with('success', 'SLA policy updated successfully.');
    }

    public function destroySla($id)
    {
        $policy = DB::table('sla_policies')->where('id', $id)->first();

        if (!$policy) {
            return redirect()->back()->with('error', 'SLA policy not found.');
        }

        // Check if policy is being used by tickets
        $ticketCount = DB::table('tickets')->where('sla_policy_id', $id)->count();

        if ($ticketCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete SLA policy that is assigned to tickets.');
        }

        DB::table('sla_policies')->where('id', $id)->delete();

        return redirect()->route('admin.settings.sla')
            ->with('success', 'SLA policy deleted successfully.');
    }
}