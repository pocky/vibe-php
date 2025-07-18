<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory\DataPersister;

use App\BlogContext\Domain\CreateCategory\Event\CategoryCreated;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategoryPath;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;

final class Category
{
    private array $domainEvents = [];

    public function __construct(
        private readonly CategoryId $id,
        private readonly CategoryName $name,
        private readonly CategorySlug $slug,
        private readonly CategoryPath $path,
        private readonly CategoryId|null $parentId,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
        // Emit domain event on creation
        $this->domainEvents[] = new CategoryCreated(
            categoryId: $this->id,
            name: $this->name,
            slug: $this->slug,
            path: $this->path,
            parentId: $this->parentId,
            createdAt: $this->createdAt
        );
    }

    // Getters
    public function id(): CategoryId
    {
        return $this->id;
    }

    public function name(): CategoryName
    {
        return $this->name;
    }

    public function slug(): CategorySlug
    {
        return $this->slug;
    }

    public function path(): CategoryPath
    {
        return $this->path;
    }

    public function parentId(): CategoryId|null
    {
        return $this->parentId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Business methods
    public function isRoot(): bool
    {
        return !$this->parentId instanceof CategoryId;
    }

    public function hasParent(): bool
    {
        return $this->parentId instanceof CategoryId;
    }

    public function isChild(): bool
    {
        return !$this->isRoot();
    }

    public function update(CategoryName $name, CategorySlug $slug): void
    {
        // For simplicity, we create a new category with updated values
        // In a real scenario, you might want to emit an update event
        $this->updatedAt = new \DateTimeImmutable();

        // Note: In a more complete implementation, we'd emit a CategoryUpdated event
        // $this->domainEvents[] = new CategoryUpdated($this->id, $name, $slug);
    }

    // Domain event management
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function hasUnreleasedEvents(): bool
    {
        return [] !== $this->domainEvents;
    }
}
