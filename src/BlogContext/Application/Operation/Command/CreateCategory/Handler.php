<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateCategory;

use App\BlogContext\Domain\CreateCategory\CreatorInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler implements HandlerInterface
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
        $description = new Description($command->description);
        $parentId = null !== $command->parentCategoryId ? new CategoryId($command->parentCategoryId) : null;
        $order = new Order($command->order);
        $createdAt = new \DateTimeImmutable();

        // Execute domain operation
        $category = ($this->creator)(
            categoryId: $categoryId,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
            createdAt: $createdAt,
        );

        // Dispatch domain events
        foreach ($category->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
