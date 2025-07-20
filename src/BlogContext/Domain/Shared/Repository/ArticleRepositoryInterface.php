<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Repository;

use App\BlogContext\Domain\CreateArticle\Model\Article as CreateArticle;
use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\UpdateArticle\Model\Article as UpdateArticle;

interface ArticleRepositoryInterface
{
    /**
     * Add a new article.
     */
    public function add(CreateArticle $article): void;

    /**
     * Update an existing article.
     */
    public function update(UpdateArticle $article): void;

    /**
     * Find an article by ID.
     */
    public function findById(ArticleId $id): ArticleReadModel|null;

    /**
     * Find an article by slug.
     */
    public function findBySlug(Slug $slug): ArticleReadModel|null;

    /**
     * Remove an article by ID.
     */
    public function remove(ArticleId $id): void;

    /**
     * Check if a slug already exists.
     */
    public function existsWithSlug(Slug $slug): bool;

    /**
     * Find all articles with pagination.
     *
     * @return ArticleReadModel[]
     */
    public function findAllPaginated(int $page = 1, int $limit = 20): array;

    /**
     * Count all articles.
     */
    public function countAll(): int;

    /**
     * Find articles by filters.
     *
     * @param array<string, mixed> $filters
     *
     * @return ArticleReadModel[]
     */
    public function findByFilters(
        array $filters,
        string|null $sortBy = null,
        string $sortOrder = 'asc',
        int $limit = 20,
        int $offset = 0
    ): array;

    /**
     * Count articles by filters.
     *
     * @param array<string, mixed> $filters
     */
    public function countByFilters(array $filters): int;

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
        string $sortOrder = 'DESC'
    ): array;
}
