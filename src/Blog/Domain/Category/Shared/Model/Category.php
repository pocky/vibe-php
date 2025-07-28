<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Model;

use App\Blog\Domain\Category\Shared\Event\CategoryCreated;
use App\Blog\Domain\Category\Shared\Event\CategoryDeleted;
use App\Blog\Domain\Category\Shared\Event\CategoryUpdated;
use App\Blog\Domain\Category\Shared\Exception\CategoryHasArticles;
use App\Blog\Domain\Category\Shared\Exception\CategoryHasChildren;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;

/**
 * Category aggregate root with rich business logic.
 */
final class Category
{
    private array $events = [];

    public function __construct(
        private readonly CategoryId $id,
        private CategoryName $categoryName,
        private CategorySlug $categorySlug,
        private Description $description,
        private CategoryId|null $parentId,
        private Order $order,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        CategoryId $id,
        CategoryName $categoryName,
        CategorySlug $categorySlug,
        Description $description,
        CategoryId|null $parentId = null,
        Order|null $order = null,
    ): self {
        $now = new \DateTimeImmutable();
        $order ??= new Order(0);

        $category = new self(
            id: $id,
            categoryName: $categoryName,
            categorySlug: $categorySlug,
            description: $description,
            parentId: $parentId,
            order: $order,
            createdAt: $now,
            updatedAt: $now,
        );

        $category->recordEvent(new CategoryCreated(
            categoryId: $id,
            categoryName: $categoryName,
            categorySlug: $categorySlug,
            description: $description,
            parentId: $parentId,
            order: $order,
            createdAt: $now,
        ));

        return $category;
    }

    public function update(
        CategoryName|null $name = null,
        CategorySlug|null $slug = null,
        Description|null $description = null,
        CategoryId|null $parentId = null,
        Order|null $order = null,
        bool $clearParent = false,
    ): void {
        $hasChanges = false;

        if ($name instanceof CategoryName && !$this->categoryName->equals($name)) {
            $this->categoryName = $name;
            $hasChanges = true;
        }

        if ($slug instanceof CategorySlug && !$this->categorySlug->equals($slug)) {
            $this->categorySlug = $slug;
            $hasChanges = true;
        }

        if ($description instanceof Description && !$this->description->equals($description)) {
            $this->description = $description;
            $hasChanges = true;
        }

        if ($clearParent && $this->parentId instanceof CategoryId) {
            $this->parentId = null;
            $hasChanges = true;
        } elseif ($parentId instanceof CategoryId && $this->parentId?->getValue() !== $parentId->getValue()) {
            // Prevent self-reference
            if ($this->id->equals($parentId)) {
                throw new \InvalidArgumentException('Category cannot be its own parent');
            }

            $this->parentId = $parentId;
            $hasChanges = true;
        }

        if ($order instanceof Order && !$this->order->equals($order)) {
            $this->order = $order;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $this->updatedAt = new \DateTimeImmutable();

            $this->recordEvent(new CategoryUpdated(
                categoryId: $this->id,
                categoryName: $this->categoryName,
                categorySlug: $this->categorySlug,
                description: $this->description,
                parentId: $this->parentId,
                order: $this->order,
                updatedAt: $this->updatedAt,
            ));
        }
    }

    public function delete(int $childCount = 0, int $articleCount = 0): void
    {
        if (0 < $childCount) {
            throw new CategoryHasChildren($this->id, $childCount);
        }

        if (0 < $articleCount) {
            throw new CategoryHasArticles($this->id, $articleCount);
        }

        $this->recordEvent(new CategoryDeleted(
            categoryId: $this->id,
            deletedAt: new \DateTimeImmutable(),
        ));
    }

    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    // Getters
    public function getId(): CategoryId
    {
        return $this->id;
    }

    public function getName(): CategoryName
    {
        return $this->categoryName;
    }

    public function getSlug(): CategorySlug
    {
        return $this->categorySlug;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getParentId(): CategoryId|null
    {
        return $this->parentId;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isRoot(): bool
    {
        return !$this->parentId instanceof CategoryId;
    }

    public function hasParent(): bool
    {
        return $this->parentId instanceof CategoryId;
    }
}
