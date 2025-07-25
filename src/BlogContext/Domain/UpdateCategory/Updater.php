<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateCategory;

use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;
use App\BlogContext\Domain\UpdateCategory\Event\CategoryUpdated;
use App\BlogContext\Domain\UpdateCategory\Exception\CategoryNotFound;
use App\BlogContext\Domain\UpdateCategory\Exception\SlugAlreadyExists;
use App\BlogContext\Domain\UpdateCategory\Model\Category;

final readonly class Updater implements UpdaterInterface
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
    ) {
    }

    #[\Override]
    public function __invoke(
        CategoryId $categoryId,
        CategoryName $name,
        CategorySlug $slug,
        Description $description,
        CategoryId|null $parentId,
        Order $order,
    ): Category {
        // Find the category
        $existingCategory = $this->repository->findById($categoryId);
        if (!$existingCategory instanceof \App\BlogContext\Domain\Shared\Model\Category) {
            throw CategoryNotFound::withId($categoryId);
        }

        // Business rule: Check if new slug is unique (if changed)
        if (!$existingCategory->slug->equals($slug) && $this->repository->existsBySlug($slug)) {
            throw SlugAlreadyExists::forSlug($slug);
        }

        // Business rule: Check if new name is unique (if changed)
        if (!$existingCategory->name->equals($name) && $this->repository->existsByName($name)) {
            throw new \InvalidArgumentException('Category with this name already exists');
        }

        // Business rule: Validate parent exists if provided
        if ($parentId instanceof CategoryId && !$this->repository->existsById($parentId)) {
            throw new \InvalidArgumentException('Parent category does not exist');
        }

        // Business rule: Maximum hierarchy depth of 2 levels (parent-child only)
        if ($parentId instanceof CategoryId) {
            // Check if this category has children
            $children = $this->repository->findByParentId($categoryId);
            if (0 < count($children)) {
                throw new \InvalidArgumentException('Cannot set parent for category that has children');
            }

            // Check if parent has parent
            $parent = $this->repository->findById($parentId);
            if ($parent && $parent->hasParent()) {
                throw new \InvalidArgumentException('Categories can only have 2 levels of hierarchy');
            }
        }

        // Create updated category model
        $category = Category::update(
            id: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
            createdAt: $existingCategory->createdAt,
        );

        // Create domain event
        $event = new CategoryUpdated(
            categoryId: $categoryId->getValue(),
            name: $name->getValue(),
            slug: $slug->getValue(),
            description: $description->getValue(),
            parentId: $parentId?->getValue(),
            order: $order->getValue(),
            updatedAt: $category->updatedAt,
        );

        // Attach event to category
        $categoryWithEvents = $category->withEvents([$event]);

        // Persist
        $this->repository->save($categoryWithEvents);

        // Return aggregate with unreleased events for Application layer to handle
        return $categoryWithEvents;
    }
}
