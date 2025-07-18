<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateCategory;

use App\BlogContext\Domain\CreateCategory\CreatorInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private CreatorInterface $creator,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $categoryId = new CategoryId($command->categoryId);
        $name = new CategoryName($command->name);
        $slug = new CategorySlug($command->slug);
        $parentId = $command->parentId ? new CategoryId($command->parentId) : null;

        // Use domain Creator
        $category = ($this->creator)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            parentId: $parentId,
            createdAt: $command->createdAt ?? new \DateTimeImmutable()
        );

        // Dispatch domain events
        foreach ($category->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
