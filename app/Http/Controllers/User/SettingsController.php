<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the user settings page.
     */
    public function index()
    {
        return Inertia::render('User/Settings');
    }
}
