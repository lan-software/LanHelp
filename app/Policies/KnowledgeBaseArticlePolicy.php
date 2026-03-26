<?php

namespace App\Policies;

use App\Models\KnowledgeBaseArticle;
use App\Models\User;

class KnowledgeBaseArticlePolicy
{
    /** Published articles are publicly readable (no auth required, handled in controller). */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, KnowledgeBaseArticle $article): bool
    {
        if ($article->is_published) {
            return true;
        }

        return $user?->isStaff() ?? false;
    }

    /** Only admins can manage KB articles. */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, KnowledgeBaseArticle $article): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, KnowledgeBaseArticle $article): bool
    {
        return $user->isAdmin();
    }
}
