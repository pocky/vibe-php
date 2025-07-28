<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Repository;

use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\Model\Article;
use App\Blog\Domain\Shared\ValueObject\Slug;

/**
 * Write repository interface for Article aggregate.
 * Contains methods for persisting, removing, and retrieving aggregates for write operations.
 * Read operations should use ArticleReadRepositoryInterface.
 */
interface ArticleWriteRepositoryInterface
{
    /**
     * Add a new article aggregate
     */
    public function add(Article $article): void;

    /**
     * Update an existing article aggregate
     */
    public function update(Article $article): void;

    /**
     * Remove an article aggregate
     */
    public function remove(Article $article): void;

    /**
     * Get an article aggregate by ID (for write operations)
     *
     * @throws \RuntimeException if article not found
     */
    public function get(ArticleId $articleId): Article;

    /**
     * Find an article aggregate by ID (returns null if not found)
     */
    public function findById(ArticleId $articleId): Article|null;

    /**
     * Find an article aggregate by slug (returns null if not found)
     */
    public function findBySlug(Slug $slug): Article|null;

    /**
     * Check if a slug already exists
     */
    public function existsWithSlug(Slug $slug): bool;
}
