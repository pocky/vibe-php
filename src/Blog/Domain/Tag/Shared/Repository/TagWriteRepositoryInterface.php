<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Repository;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\Model\Tag;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;

/**
 * Write repository interface for Tag aggregate.
 * Contains methods for reading and persisting aggregates.
 */
interface TagWriteRepositoryInterface
{
    /**
     * Add a new tag aggregate
     */
    public function add(Tag $tag): void;

    /**
     * Update an existing tag aggregate
     */
    public function update(Tag $tag): void;

    /**
     * Remove a tag aggregate
     */
    public function remove(Tag $tag): void;

    /**
     * Get tag by ID (throws exception if not found)
     */
    public function get(TagId $tagId): Tag;

    /**
     * Find tag by ID
     */
    public function findById(TagId $tagId): Tag|null;

    /**
     * Find tag by slug
     */
    public function findBySlug(TagSlug $tagSlug): Tag|null;

    /**
     * Check if tag exists with given slug, excluding specific ID
     */
    public function existsBySlugExcludingId(TagSlug $tagSlug, TagId $tagId): bool;

    /**
     * Count articles for a tag
     */
    public function countArticles(TagId $tagId): int;
}
