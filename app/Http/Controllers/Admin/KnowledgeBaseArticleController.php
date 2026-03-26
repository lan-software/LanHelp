<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PublishKnowledgeBaseArticleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKnowledgeBaseArticleRequest;
use App\Http\Requests\UpdateKnowledgeBaseArticleRequest;
use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBaseArticleController extends Controller
{
    public function index(): Response
    {
        $articles = KnowledgeBaseArticle::query()
            ->with('author')
            ->latest()
            ->paginate(30);

        return Inertia::render('admin/kb/Index', [
            'articles' => $articles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/kb/Edit', [
            'article' => null,
        ]);
    }

    public function store(StoreKnowledgeBaseArticleRequest $request, PublishKnowledgeBaseArticleAction $publishAction): RedirectResponse
    {
        $this->authorize('create', KnowledgeBaseArticle::class);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(6));
        $data['author_id'] = $request->user()->id;

        $article = KnowledgeBaseArticle::create($data);

        if ($data['is_published'] ?? false) {
            $publishAction->publish($article);
        }

        return redirect()->route('admin.kb.index')
            ->with('success', 'Article created.');
    }

    public function edit(KnowledgeBaseArticle $article): Response
    {
        $this->authorize('update', $article);

        return Inertia::render('admin/kb/Edit', [
            'article' => $article,
        ]);
    }

    public function update(UpdateKnowledgeBaseArticleRequest $request, KnowledgeBaseArticle $article, PublishKnowledgeBaseArticleAction $publishAction): RedirectResponse
    {
        $this->authorize('update', $article);

        $data = $request->validated();
        $article->update($data);

        $shouldPublish = $data['is_published'] ?? false;

        if ($shouldPublish && ! $article->is_published) {
            $publishAction->publish($article);
        } elseif (! $shouldPublish && $article->is_published) {
            $publishAction->unpublish($article);
        }

        return redirect()->route('admin.kb.index')
            ->with('success', 'Article updated.');
    }

    public function destroy(KnowledgeBaseArticle $article): RedirectResponse
    {
        $this->authorize('delete', $article);

        $article->delete();

        return redirect()->route('admin.kb.index')
            ->with('success', 'Article deleted.');
    }
}
