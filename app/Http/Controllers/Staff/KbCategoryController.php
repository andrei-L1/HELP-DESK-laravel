<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class KbCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = KbCategory::orderBy('sort_order')->withCount('articles')->get();

        return Inertia::render('Staff/KnowledgeBase/Categories/Index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/KnowledgeBase/Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        KbCategory::create($validated);

        return redirect()->route('staff.kb.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(KbCategory $category)
    {
        return Inertia::render('Staff/KnowledgeBase/Categories/Edit', [
            'category' => $category
        ]);
    }

    public function update(Request $request, KbCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        if ($category->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return redirect()->route('staff.kb.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(KbCategory $category)
    {
        if ($category->articles()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated articles.');
        }

        $category->delete();
        return redirect()->route('staff.kb.categories.index')->with('success', 'Category deleted successfully.');
    }
}
