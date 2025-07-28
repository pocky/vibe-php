<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Repository;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Model\Category;

/**
 * Write repository interface for Category aggregate.
 * Only contains methods for persisting and removing aggregates.
 * Read operations should use CategoryReadRepositoryInterface.
 */
interface CategoryWriteRepositoryInterface
{
    /**
     * Add a new category aggregate
     */
    public function add(Category $category): void;

    /**
     * Update an existing category aggregate
     */
    public function update(Category $category): void;

    /**
     * Remove a category aggregate
     */
    public function remove(Category $category): void;

    /**
     * Get category by ID
     */
    public function get(CategoryId $categoryId): Category;

    /**
     * Check if a slug already exists
     */
    public function existsWithSlug(\App\Blog\Domain\Shared\ValueObject\Slug $slug): bool;

    /**
     * Check if a category exists by ID
     */
    public function existsById(CategoryId $categoryId): bool;

    /**
     * Check if a category exists by slug
     */
    public function existsBySlug(\App\Blog\Domain\Category\Shared\ValueObject\CategorySlug $categorySlug): bool;

    /**
     * Check if a category exists by name
     */
    public function existsByName(\App\Blog\Domain\Category\Shared\ValueObject\CategoryName $categoryName): bool;
}
