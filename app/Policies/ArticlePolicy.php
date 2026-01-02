<?php

declare(strict_types=1);

namespace App\Policies;

use AichaDigital\Larabill\Models\Article;
use App\Models\User;

/**
 * Authorization policy for Article model.
 *
 * @see ADR-004 for authorization architecture
 */
class ArticlePolicy
{
    /**
     * Determine whether the user can view any articles.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view article list
        return true;
    }

    /**
     * Determine whether the user can view the article.
     */
    public function view(User $user, Article $article): bool
    {
        // All authenticated users can view articles
        return true;
    }

    /**
     * Determine whether the user can create articles.
     */
    public function create(User $user): bool
    {
        // Only admin can create articles
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the article.
     */
    public function update(User $user, Article $article): bool
    {
        // Only admin can update articles
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the article.
     */
    public function delete(User $user, Article $article): bool
    {
        // Only admin can delete articles
        if (! $user->isAdmin()) {
            return false;
        }

        // Cannot delete article used in invoices
        if ($article->invoiceItems()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the article.
     */
    public function restore(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the article.
     */
    public function forceDelete(User $user, Article $article): bool
    {
        // Force delete not allowed for articles with history
        return false;
    }
}
