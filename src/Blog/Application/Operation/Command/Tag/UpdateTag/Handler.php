<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Tag\UpdateTag;

use App\Blog\Domain\Tag\Shared\Identifier\TagId;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Domain\Tag\TagUpdater;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private TagUpdater $updater,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Convert string values to domain value objects
        $tagId = new TagId($command->tagId);
        $name = '' !== $command->name && '0' !== $command->name ? new TagName($command->name) : null;
        $slug = '' !== $command->slug && '0' !== $command->slug ? new TagSlug($command->slug) : null;

        // Call domain updater to get model with domain events
        $tag = ($this->updater)(
            tagId: $tagId,
            name: $name,
            slug: $slug,
        );

        // Dispatch domain events via EventBus (if events exist)
        foreach ($tag->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
