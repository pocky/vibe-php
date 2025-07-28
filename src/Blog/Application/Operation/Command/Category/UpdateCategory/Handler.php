<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Category\UpdateCategory;

use App\Blog\Domain\Category\CategoryUpdater;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private CategoryUpdater $updater,
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

        $parentId = null;
        if (null !== $command->parentId) {
            $parentId = new CategoryId($command->parentId);
        }

        $order = null;
        if (null !== $command->order) {
            $order = new Order($command->order);
        }

        // Execute domain operation
        $category = ($this->updater)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
            clearParent: $command->clearParent,
        );

        // Dispatch domain events
        foreach ($category->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
