<?php

namespace App\Actions;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Support\Carbon;

class PublishKnowledgeBaseArticleAction
{
    public function publish(KnowledgeBaseArticle $article): void
    {
        $article->update([
            'is_published' => true,
            'published_at' => $article->published_at ?? Carbon::now(),
        ]);
    }

    public function unpublish(KnowledgeBaseArticle $article): void
    {
        $article->update(['is_published' => false]);
    }
}
