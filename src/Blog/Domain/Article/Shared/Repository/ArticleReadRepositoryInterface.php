<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Repository;

use App\Blog\Application\Shared\ReadModel\ArticleReadModel;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Shared\ValueObject\Slug;

/**
 * Repository interface for article read operations.
 * Used by query handlers and read operations.
 */
interface ArticleReadRepositoryInterface
{
    /**
     * Find an article by ID.
     */
    public function findById(ArticleId $articleId): ArticleReadModel|null;

    /**
     * Find an article by slug.
     */
    public function findBySlug(Slug $slug): ArticleReadModel|null;

    /**
     * Check if a slug already exists.
     */
    public function existsWithSlug(Slug $slug): bool;

    /**
     * Find articles by criteria with pagination.
     *
     * @return array{articles: ArticleReadModel[], total: int}
     */
    public function findByCriteria(
        ArticleStatus|null $status = null,
        string|null $authorId = null,
        int $limit = 20,
        int $offset = 0,
        string $sortBy = 'createdAt',
        string $sortOrder = 'DESC',
    ): array;

    /**
     * Find articles by author ID.
     *
     * @return array<array{id: string, title: string, slug: string, status: string, publishedAt: string|null}>
     */
    public function findByAuthorId(AuthorId $authorId, int $limit, int $offset): array;

    /**
     * Count articles by author ID.
     */
    public function countByAuthorId(AuthorId $authorId): int;
}
