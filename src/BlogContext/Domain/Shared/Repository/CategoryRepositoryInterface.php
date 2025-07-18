<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Repository;

use App\BlogContext\Domain\CreateCategory\DataPersister\Category;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryPath;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;

    public function findById(CategoryId $id): Category|null;

    public function findBySlug(CategorySlug $slug): Category|null;

    public function findByPath(CategoryPath $path): Category|null;

    public function existsBySlug(CategorySlug $slug): bool;

    public function existsByPath(CategoryPath $path): bool;

    public function findRootCategories(): array;

    public function findChildrenByParentId(CategoryId $parentId): array;

    public function deleteById(CategoryId $id): void;

    /**
     * @return Category[]
     */
    public function findAll(): array;
}
