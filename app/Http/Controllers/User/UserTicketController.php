<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserTicketController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Tickets/Index');
    }

    public function create()
    {
        return Inertia::render('User/Tickets/Create');
    }

    public function show($id)
    {
        return Inertia::render('User/Tickets/Show', ['ticket_id' => $id]);
    }
}
