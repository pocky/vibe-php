<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\DeleteCategory;

use App\BlogContext\Domain\DeleteCategory\DeleterInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private DeleterInterface $deleter,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $categoryId = new CategoryId($command->categoryId);

        // Execute domain operation
        $category = ($this->deleter)(
            categoryId: $categoryId,
        );

        // Dispatch domain events
        foreach ($category->getEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
