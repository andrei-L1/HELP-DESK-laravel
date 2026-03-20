<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the user.
     */
    public function index()
    {
        return \Inertia\Inertia::render('Notifications/Index', [
            'notifications' => auth()->user()->notifications()->paginate(20),
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    /**
     * Mark all notifications for the user as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}

