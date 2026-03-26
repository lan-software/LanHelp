<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request): Response
    {
        $category = $request->query('category');

        $articles = KnowledgeBaseArticle::query()
            ->published()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug', 'excerpt', 'category', 'published_at']);

        $categories = KnowledgeBaseArticle::query()
            ->published()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('kb/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => ['category' => $category],
        ]);
    }

    public function show(KnowledgeBaseArticle $article): Response
    {
        $this->authorize('view', $article);

        return Inertia::render('kb/Show', [
            'article' => $article->load('author'),
        ]);
    }
}
