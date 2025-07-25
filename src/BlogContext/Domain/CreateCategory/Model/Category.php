<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory\Model;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;

final readonly class Category
{
    public function __construct(
        public CategoryId $id,
        public CategoryName $name,
        public CategorySlug $slug,
        public Description $description,
        public CategoryId|null $parentId,
        public Order $order,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        private array $events = []
    ) {
    }

    public static function create(
        CategoryId $id,
        CategoryName $name,
        CategorySlug $slug,
        Description $description,
        CategoryId|null $parentId,
        Order $order
    ): self {
        $now = new \DateTimeImmutable();

        return new self(
            id: $id,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            slug: $this->slug,
            description: $this->description,
            parentId: $this->parentId,
            order: $this->order,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
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
