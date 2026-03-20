<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function notifications()
    {
        return Inertia::render('Agent/Settings');
    }

    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_tickets' => 'nullable|boolean',
            'browser_tickets' => 'nullable|boolean',
            // Add other frontend notification fields here as needed
        ]);

        Setting::set('user_' . Auth::id() . '_notification_settings', $validated, 'json', 'user_' . Auth::id());

        return back()->with('success', 'Notification settings updated.');
    }

    public function signature()
    {
        $signature = Setting::get('user_' . Auth::id() . '_email_signature', '');

        return Inertia::render('Agent/Settings/Signature', [
            'signature' => $signature,
        ]);
    }

    public function updateSignature(Request $request)
    {
        $validated = $request->validate([
            'signature' => 'nullable|string|max:2000',
        ]);

        Setting::set('user_' . Auth::id() . '_email_signature', $validated['signature'] ?? '', 'string', 'user_' . Auth::id());

        return back()->with('success', 'Signature updated.');
    }
}