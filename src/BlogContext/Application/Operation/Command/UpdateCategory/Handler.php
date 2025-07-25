<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateCategory;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;
use App\BlogContext\Domain\UpdateCategory\UpdaterInterface;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private UpdaterInterface $updater,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $categoryId = new CategoryId($command->categoryId);
        $name = new CategoryName($command->name);
        $slug = new CategorySlug($command->slug);
        $description = new Description($command->description);
        $parentId = null !== $command->parentId ? new CategoryId($command->parentId) : null;
        $order = new Order($command->order);

        // Execute domain operation
        $category = ($this->updater)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
        );

        // Dispatch domain events
        foreach ($category->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
