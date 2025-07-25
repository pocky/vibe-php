<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Repository;

use App\BlogContext\Domain\Shared\Model\Category;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Slug;

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;

    public function findById(CategoryId $id): Category|null;

    public function existsById(CategoryId $id): bool;

    public function existsBySlug(CategorySlug $slug): bool;

    public function existsWithSlug(Slug $slug): bool;

    public function existsByName(CategoryName $name): bool;

    /**
     * @return Category[]
     */
    public function findAll(): array;

    /**
     * @return Category[]
     */
    public function findByParentId(CategoryId|null $parentId): array;

    /**
     * @return Category[]
     */
    public function findRootCategories(): array;

    public function delete(Category $category): void;

    public function countArticlesByCategory(CategoryId $categoryId): int;
}
