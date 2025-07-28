<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Repository;

use App\Blog\Application\Shared\ReadModel\TagReadModel;
use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;

/**
 * Repository interface for tag read operations.
 * Used by query handlers and read operations.
 */
interface TagReadRepositoryInterface
{
    /**
     * Find a tag by ID.
     */
    public function findById(TagId $tagId): TagReadModel|null;

    /**
     * Find a tag by slug.
     */
    public function findBySlug(TagSlug $tagSlug): TagReadModel|null;

    /**
     * Check if a tag exists by slug.
     */
    public function existsBySlug(TagSlug $tagSlug): bool;

    /**
     * Find all tags ordered by name.
     *
     * @return TagReadModel[]
     */
    public function findAll(): array;

    /**
     * Find tags by name pattern for autocomplete/search.
     *
     * @return TagReadModel[]
     */
    public function findByNamePattern(string $namePattern, int $limit = 10): array;

    /**
     * Find tags with no articles (unused tags).
     *
     * @return TagReadModel[]
     */
    public function findUnusedTags(): array;

    /**
     * Find popular tags ordered by article count.
     *
     * @return array<array{tag: TagReadModel, articleCount: int}>
     */
    public function findPopularTags(int $limit = 20): array;

    /**
     * Find tags with pagination.
     *
     * @return array{tags: TagReadModel[], total: int}
     */
    public function findPaginated(int $limit, int $offset): array;
}
