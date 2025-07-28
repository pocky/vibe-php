<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Tag\DeleteTag;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\TagDeleter;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private TagDeleter $deleter,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Convert string values to domain value objects
        $tagId = new TagId($command->tagId);

        // Call domain deleter to get model with domain events
        $tag = ($this->deleter)(
            tagId: $tagId,
        );

        // Dispatch domain events via EventBus (if events exist)
        foreach ($tag->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
