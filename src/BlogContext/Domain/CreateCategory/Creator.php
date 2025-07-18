<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory;

use App\BlogContext\Domain\CreateCategory\DataPersister\Category;
use App\BlogContext\Domain\CreateCategory\Exception\CategoryAlreadyExists;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategoryPath;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;

final readonly class Creator implements CreatorInterface
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        CategoryId $categoryId,
        CategoryName $name,
        CategorySlug $slug,
        CategoryId|null $parentId = null,
        \DateTimeImmutable|null $createdAt = null,
    ): Category {
        // Business validations
        if ($this->repository->existsBySlug($slug)) {
            throw new CategoryAlreadyExists($slug);
        }

        // Create path based on parent
        $path = $this->buildCategoryPath($slug, $parentId);

        if ($this->repository->existsByPath($path)) {
            throw new CategoryAlreadyExists($slug);
        }

        // Validate hierarchy depth (max 2 levels)
        if (2 < $path->getDepth()) {
            throw new \InvalidArgumentException('Maximum category depth reached (2 levels)');
        }

        // Create the aggregate
        $category = new Category(
            id: $categoryId,
            name: $name,
            slug: $slug,
            path: $path,
            parentId: $parentId,
            createdAt: $createdAt ?? new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        // Persist
        $this->repository->save($category);

        // Return aggregate with unreleased events for Application layer to handle
        return $category;
    }

    private function buildCategoryPath(CategorySlug $slug, CategoryId|null $parentId): CategoryPath
    {
        if (!$parentId instanceof CategoryId) {
            // Root category
            return new CategoryPath($slug->getValue());
        }

        // Find parent category to build path
        $parent = $this->repository->findById($parentId);
        if (!$parent instanceof Category) {
            throw new \InvalidArgumentException('Parent category not found');
        }

        // Build child path
        return $parent->path()->appendChild($slug->getValue());
    }
}
