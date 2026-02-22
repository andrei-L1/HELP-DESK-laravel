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
        // Get ticket settings
        $statuses = DB::table('ticket_statuses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $priorities = DB::table('ticket_priorities')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = DB::table('ticket_categories')
            ->orderBy('name')
            ->get();

        $settings = [
            'default_status' => config('tickets.default_status', 1),
            'default_priority' => config('tickets.default_priority', 1),
            'allow_attachments' => config('tickets.allow_attachments', true),
            'max_attachments' => config('tickets.max_attachments', 5),
            'max_attachment_size' => config('tickets.max_attachment_size', 2048),
            'auto_assign' => config('tickets.auto_assign', false),
        ];

        return Inertia::render('Admin/Settings/Ticket', [
            'statuses' => $statuses,
            'priorities' => $priorities,
            'categories' => $categories,
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
            'mail_encryption' => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
        ];

        return Inertia::render('Admin/Settings/Email', [
            'settings' => $settings
        ]);
    }

    /**
     * SLA Policies.
     */
    public function sla()
    {
        $policies = DB::table('sla_policies')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Admin/Settings/SLA', [
            'policies' => $policies
        ]);
    }
}