<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Category\DeleteCategory;

use App\Blog\Domain\Category\CategoryDeleter;
use App\Blog\Domain\Category\Shared\Event\CategoryDeleted;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private CategoryDeleter $deleter,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $categoryId = new CategoryId($command->categoryId);

        // We need a custom implementation here because the CategoryDeleter
        // doesn't return the category with events. Let's handle it differently.

        // For now, let's dispatch a deletion event manually
        // This is a compromise until we refactor the domain services to be more consistent

        ($this->deleter)($categoryId);

        // Since the domain service doesn't expose events, we'll create a simple event
        // This would be better handled by modifying the domain service to return events
        $event = new CategoryDeleted(
            categoryId: $categoryId,
            deletedAt: new \DateTimeImmutable()
        );

        ($this->eventBus)($event);
    }
}
