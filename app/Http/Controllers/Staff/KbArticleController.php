<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\KbCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class KbArticleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $status = $request->query('status'); // published, draft, internal

        $query = KbArticle::query()->with(['category', 'author']);

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status === 'published') {
            $query->where('is_published', true)->where('is_internal', false);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        } elseif ($status === 'internal') {
            $query->where('is_internal', true);
        }

        $articles = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = KbCategory::orderBy('sort_order')->get();

        return Inertia::render('Staff/KnowledgeBase/Articles/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => $request->only('search', 'category_id', 'status')
        ]);
    }

    public function create()
    {
        $categories = KbCategory::where('is_active', true)->orderBy('sort_order')->get();
        return Inertia::render('Staff/KnowledgeBase/Articles/Create', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:kb_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'is_internal' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        $validated['author_id'] = Auth::id();

        KbArticle::create($validated);

        return redirect()->route('staff.kb.articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(KbArticle $article)
    {
        $categories = KbCategory::orderBy('sort_order')->get();
        return Inertia::render('Staff/KnowledgeBase/Articles/Edit', [
            'article' => $article,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, KbArticle $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:kb_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'is_internal' => 'boolean',
        ]);

        if ($article->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        }

        $article->update($validated);

        return redirect()->route('staff.kb.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(KbArticle $article)
    {
        $article->delete();
        return redirect()->route('staff.kb.articles.index')->with('success', 'Article deleted successfully.');
    }
}
