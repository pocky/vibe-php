<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory;

use App\BlogContext\Domain\CreateCategory\Event\CategoryCreated;
use App\BlogContext\Domain\CreateCategory\Exception\CategoryAlreadyExists;
use App\BlogContext\Domain\CreateCategory\Model\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;

final readonly class Creator implements CreatorInterface
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
        \DateTimeImmutable $createdAt,
    ): Category {
        // Business rule: Check if category with same slug already exists
        if ($this->repository->existsBySlug($slug)) {
            throw CategoryAlreadyExists::withSlug($slug);
        }

        // Business rule: Check if category with same name already exists
        if ($this->repository->existsByName($name)) {
            throw CategoryAlreadyExists::withName($name);
        }

        // Business rule: Validate parent exists if provided
        if ($parentId instanceof CategoryId && !$this->repository->existsById($parentId)) {
            throw new \InvalidArgumentException('Parent category does not exist');
        }

        // Business rule: Maximum hierarchy depth of 2 levels (parent-child only)
        if ($parentId instanceof CategoryId) {
            $parent = $this->repository->findById($parentId);
            if ($parent && $parent->hasParent()) {
                throw new \InvalidArgumentException('Categories can only have 2 levels of hierarchy');
            }
        }

        // Create domain model
        $category = Category::create(
            id: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
        );

        // Create domain event
        $event = new CategoryCreated(
            categoryId: $categoryId->getValue(),
            name: $name->getValue(),
            slug: $slug->getValue(),
            description: $description->getValue(),
            parentId: $parentId?->getValue(),
            order: $order->getValue(),
            createdAt: $category->createdAt,
        );

        // Attach event to category
        $categoryWithEvents = $category->withEvents([$event]);

        // Persist
        $this->repository->save($categoryWithEvents);

        // Return aggregate with unreleased events for Application layer to handle
        return $categoryWithEvents;
    }
}
