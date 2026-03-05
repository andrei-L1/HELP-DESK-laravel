<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketCategory;
use App\Models\TicketType;

class TicketSettingsController extends Controller
{
    // --- Priorities ---
    public function storePriority(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:40|unique:ticket_priorities,name',
            'level' => 'required|integer|min:1',
            'color_hex' => 'nullable|string|max:7',
            'sort_order' => 'integer'
        ]);
        
        TicketPriority::create($validated);
        return redirect()->back()->with('success', 'Priority created successfully.');
    }

    public function updatePriority(Request $request, $id)
    {
        $priority = TicketPriority::findOrFail($id);
        $validated = $request->validate([
            'name' => "required|string|max:40|unique:ticket_priorities,name,{$id}",
            'level' => 'required|integer|min:1',
            'color_hex' => 'nullable|string|max:7',
            'sort_order' => 'integer'
        ]);

        $priority->update($validated);
        return redirect()->back()->with('success', 'Priority updated successfully.');
    }

    public function destroyPriority($id)
    {
        $priority = TicketPriority::findOrFail($id);
        if ($priority->tickets()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete priority that has tickets.');
        }
        $priority->delete();
        return redirect()->back()->with('success', 'Priority deleted successfully.');
    }

    // --- Statuses ---
    public function storeStatus(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:40|unique:ticket_statuses,name',
            'title' => 'required|string|max:80',
            'is_active' => 'boolean',
            'is_closed' => 'boolean',
            'is_resolved' => 'boolean',
            'sort_order' => 'integer',
            'color_hex' => 'nullable|string|max:7'
        ]);

        TicketStatus::create($validated);
        return redirect()->back()->with('success', 'Status created successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $status = TicketStatus::findOrFail($id);
        $validated = $request->validate([
            'name' => "required|string|max:40|unique:ticket_statuses,name,{$id}",
            'title' => 'required|string|max:80',
            'is_active' => 'boolean',
            'is_closed' => 'boolean',
            'is_resolved' => 'boolean',
            'sort_order' => 'integer',
            'color_hex' => 'nullable|string|max:7'
        ]);

        $status->update($validated);
        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    public function destroyStatus($id)
    {
        $status = TicketStatus::findOrFail($id);
        if ($status->tickets()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete status that is assigned to tickets.');
        }
        $status->delete();
        return redirect()->back()->with('success', 'Status deleted successfully.');
    }

    // --- Categories ---
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:ticket_categories,name',
            'title' => 'required|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        TicketCategory::create($validated);
        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = TicketCategory::findOrFail($id);
        $validated = $request->validate([
            'name' => "required|string|max:50|unique:ticket_categories,name,{$id}",
            'title' => 'required|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        $category->update($validated);
        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroyCategory($id)
    {
        $category = TicketCategory::findOrFail($id);
        // Note: No foreign key checking done for categories based on models right now
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }

    // --- Types ---
    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:ticket_types,name',
            'title' => 'required|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        TicketType::create($validated);
        return redirect()->back()->with('success', 'Type created successfully.');
    }

    public function updateType(Request $request, $id)
    {
        $type = TicketType::findOrFail($id);
        $validated = $request->validate([
            'name' => "required|string|max:50|unique:ticket_types,name,{$id}",
            'title' => 'required|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ]);

        $type->update($validated);
        return redirect()->back()->with('success', 'Type updated successfully.');
    }

    public function destroyType($id)
    {
        $type = TicketType::findOrFail($id);
        // Note: No foreign key checking done for types based on models right now
        $type->delete();
        return redirect()->back()->with('success', 'Type deleted successfully.');
    }
}
