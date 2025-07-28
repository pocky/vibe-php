<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Repository;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Slug;

/**
 * Repository interface for category read operations.
 * Used by query handlers and read operations.
 */
interface CategoryReadRepositoryInterface
{
    /**
     * Find a category by ID.
     */
    public function findById(CategoryId $categoryId): CategoryReadModel|null;

    /**
     * Find a category by slug.
     */
    public function findBySlug(CategorySlug $categorySlug): CategoryReadModel|null;

    /**
     * Check if a category exists by ID.
     */
    public function existsById(CategoryId $categoryId): bool;

    /**
     * Check if a category exists by slug.
     */
    public function existsBySlug(CategorySlug $categorySlug): bool;

    /**
     * Check if a slug already exists.
     */
    public function existsWithSlug(Slug $slug): bool;

    /**
     * Check if a category exists by name.
     */
    public function existsByName(CategoryName $categoryName): bool;

    /**
     * Find all categories ordered by name.
     *
     * @return CategoryReadModel[]
     */
    public function findAll(): array;

    /**
     * Find categories by parent ID.
     *
     * @return CategoryReadModel[]
     */
    public function findByParentId(CategoryId|null $parentId): array;

    /**
     * Find root categories (categories with no parent).
     *
     * @return CategoryReadModel[]
     */
    public function findRootCategories(): array;

    /**
     * Find categories tree structure with children.
     *
     * @return array<array{category: CategoryReadModel, children: CategoryReadModel[]}>
     */
    public function findCategoryTree(): array;

    /**
     * Count articles in a specific category.
     */
    public function countArticlesByCategory(CategoryId $categoryId): int;
}
