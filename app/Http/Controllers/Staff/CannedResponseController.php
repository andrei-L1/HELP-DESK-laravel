<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CannedResponseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Management UI (Inertia)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $search   = $request->query('search');
        $category = $request->query('category');
        $user     = Auth::user();

        $query = CannedResponse::with('creator')
            ->visibleTo($user->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title',    'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('shortcut','like', "%{$search}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        $responses  = $query->orderByDesc('use_count')->orderBy('title')->paginate(20)->withQueryString();
        $categories = CannedResponse::visibleTo($user->id)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('Staff/CannedResponses/Index', [
            'responses'  => $responses,
            'categories' => $categories,
            'filters'    => $request->only('search', 'category'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/CannedResponses/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'shortcut'  => 'nullable|string|max:50|regex:/^\/[a-z0-9_-]+$/|unique:canned_responses,shortcut',
            'category'  => 'nullable|string|max:100',
            'is_shared' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        CannedResponse::create($validated);

        return redirect()->route('staff.canned-responses.index')
            ->with('success', 'Canned response created.');
    }

    public function edit(CannedResponse $cannedResponse)
    {
        $this->authorizeAccess($cannedResponse);

        return Inertia::render('Staff/CannedResponses/Edit', [
            'response' => $cannedResponse,
        ]);
    }

    public function update(Request $request, CannedResponse $cannedResponse)
    {
        $this->authorizeAccess($cannedResponse);

        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'shortcut'  => [
                'nullable', 'string', 'max:50',
                'regex:/^\/[a-z0-9_-]+$/',
                "unique:canned_responses,shortcut,{$cannedResponse->id}",
            ],
            'category'  => 'nullable|string|max:100',
            'is_shared' => 'boolean',
        ]);

        $cannedResponse->update($validated);

        return redirect()->route('staff.canned-responses.index')
            ->with('success', 'Canned response updated.');
    }

    public function destroy(CannedResponse $cannedResponse)
    {
        $this->authorizeAccess($cannedResponse);

        $cannedResponse->delete();

        return redirect()->route('staff.canned-responses.index')
            ->with('success', 'Canned response deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | JSON API — used by the ticket reply picker (fetch-based, no Inertia)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/canned-responses?search=...
     * Returns a lightweight JSON list for the in-reply picker.
     */
    public function search(Request $request)
    {
        $search = $request->query('search', '');
        $user   = Auth::user();

        $results = CannedResponse::visibleTo($user->id)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title',    'like', "%{$search}%")
                          ->orWhere('shortcut','like', "%{$search}%");
                });
            })
            ->orderByDesc('use_count')
            ->orderBy('title')
            ->limit(15)
            ->get(['id', 'title', 'content', 'shortcut', 'category']);

        return response()->json($results);
    }

    /**
     * POST /api/canned-responses/{id}/use
     * Increments the use_count when an agent inserts the response.
     */
    public function incrementUse(CannedResponse $cannedResponse)
    {
        $cannedResponse->increment('use_count');
        return response()->json(['ok' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function authorizeAccess(CannedResponse $cr): void
    {
        $user = Auth::user();
        // Admins + managers can edit any; agents/others only their own
        if (! $user->isAdmin() && ! $user->isManager() && $cr->created_by !== $user->id) {
            abort(403, 'You can only edit your own canned responses.');
        }
    }
}
