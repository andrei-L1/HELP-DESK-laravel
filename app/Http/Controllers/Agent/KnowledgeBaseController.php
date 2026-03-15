<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        // Currently disabled to preserve models and migrations.
        // Returns an empty paginator for now.
        $articles = new LengthAwarePaginator([], 0, 12, 1, ['path' => $request->url()]);

        return Inertia::render('Agent/Knowledge/Index', [
            'articles' => $articles,
            'filters'  => $request->only('search'),
        ]);
    }

    public function show($article)
    {
        // Using generic parameter instead of model binding since model doesn't exist
        return Inertia::render('Agent/Knowledge/Show', [
            'article' => null,
        ]);
    }
}