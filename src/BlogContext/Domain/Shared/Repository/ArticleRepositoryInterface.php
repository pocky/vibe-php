<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Repository;

use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Slug};
use App\Shared\Infrastructure\Paginator\PaginatorInterface;

interface ArticleRepositoryInterface
{
    /**
     * Save any article model (polymorphic)
     */
    public function save(object $article): void;

    /**
     * Find article by ID and return domain model
     */
    public function findById(ArticleId $id): Article|null;

    public function existsBySlug(Slug $slug): bool;

    /**
     * Remove any article model (polymorphic)
     */
    public function remove(object $article): void;

    /**
     * Find articles with pagination and filtering
     *
     * @param array<string, mixed> $filters
     */
    public function findAllPaginated(int $page, int $limit, array $filters = []): PaginatorInterface;
}
