<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicKbController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $categories = \App\Models\KbCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->withCount(['articles' => function($q) {
                $q->where('is_published', true)->where('is_internal', false);
            }])
            ->get();

        $articles = [];
        if ($search) {
            $articles = \App\Models\KbArticle::where('is_published', true)
                ->where('is_internal', false)
                ->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                })
                ->orderBy('views', 'desc')
                ->paginate(15);
        } else {
            // Get popular articles
            $articles = \App\Models\KbArticle::where('is_published', true)
                ->where('is_internal', false)
                ->orderBy('views', 'desc')
                ->take(6)
                ->get();
        }

        return \Inertia\Inertia::render('PublicKb/Index', [
            'categories' => $categories,
            'articles' => $articles,
            'filters' => $request->only('search')
        ]);
    }

    public function category($slug)
    {
        $all_categories = \App\Models\KbCategory::where('is_active', true)->orderBy('sort_order')->get();
        $category = \App\Models\KbCategory::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $articles = \App\Models\KbArticle::where('category_id', $category->id)
            ->where('is_published', true)
            ->where('is_internal', false)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return \Inertia\Inertia::render('PublicKb/Category', [
            'all_categories' => $all_categories,
            'category' => $category,
            'articles' => $articles
        ]);
    }

    public function show($slug)
    {
        $all_categories = \App\Models\KbCategory::where('is_active', true)->orderBy('sort_order')->get();
        $article = \App\Models\KbArticle::where('slug', $slug)
            ->where('is_published', true)
            ->where('is_internal', false)
            ->with(['author', 'category'])
            ->firstOrFail();

        // Increment views
        $article->increment('views');

        $relatedArticles = \App\Models\KbArticle::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->where('is_internal', false)
            ->inRandomOrder()
            ->take(5)
            ->get();

        return \Inertia\Inertia::render('PublicKb/Show', [
            'all_categories' => $all_categories,
            'article' => $article,
            'relatedArticles' => $relatedArticles
        ]);
    }
}
