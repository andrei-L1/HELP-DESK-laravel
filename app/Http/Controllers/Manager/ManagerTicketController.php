<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Admin\AdminTicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerTicketController extends AdminTicketController
{
    /**
     * Display a paginated list of ALL tickets for managers.
     */
    public function all(Request $request)
    {
        return $this->getTickets($request, null, 'all', 'Manager/Tickets/All');
    }
    public function index(Request $request)
    {
        return $this->getTickets($request, null, 'index', 'Manager/Tickets/Index'); 
    }

    public function open(Request $request)
    {
        return $this->getTickets($request, 'Open', 'open', 'Manager/Tickets/Open');
    }
    public function assigned(Request $request)
    {
        $assignedTo = Auth::id();
        return $this->getTickets($request, null, 'assigned', 'Manager/Tickets/Assigned', $assignedTo);
    }

    /**
     * Show a single ticket (detail page) with assign option.
     */
    public function show(int $id)
    {
        $response = parent::show($id);
        if (is_a($response, \Inertia\Response::class)) {
            $reflection = new \ReflectionClass($response);
            $property = $reflection->getProperty('component');
            $property->setAccessible(true);
            $property->setValue($response, 'Manager/Tickets/Show');
        }
        return $response;
    }

    public function update(Request $request, int $id)
    {
        parent::update($request, $id);
        return redirect()->route('manager.tickets.show', $id);
    }

    public function storeMessage(Request $request, int $id)
    {
        parent::storeMessage($request, $id);
        return redirect()->route('manager.tickets.show', $id);
    }

    public function storeAttachment(Request $request, int $id)
    {
        parent::storeAttachment($request, $id);
        return redirect()->route('manager.tickets.show', $id);
    }

    public function create()
    {
        $response = parent::create();
        if (is_a($response, \Inertia\Response::class)) {
            $reflection = new \ReflectionClass($response);
            $property = $reflection->getProperty('component');
            $property->setAccessible(true);
            $property->setValue($response, 'Manager/Tickets/Create');
        }
        return $response;
    }

    public function store(\App\Http\Requests\StoreTicketRequest $request)
    {
        parent::store($request);
        return redirect()->route('manager.tickets.index');
    }
}
