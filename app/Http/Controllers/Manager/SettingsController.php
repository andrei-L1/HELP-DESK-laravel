<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the manager settings page.
     */
    public function index()
    {
        return Inertia::render('Manager/Settings');
    }
}
