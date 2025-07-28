<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Tag\CreateTag;

use App\Blog\Application\Shared\Generator\TagIdGeneratorInterface;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use App\Blog\Domain\Tag\TagCreator;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class Handler
{
    public function __construct(
        private TagCreator $creator,
        private TagIdGeneratorInterface $tagIdGenerator,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Generate new tag ID
        $tagId = $this->tagIdGenerator->nextIdentity();

        // Convert command data to value objects
        $tagName = new TagName($command->name);
        $slug = null !== $command->slug && '' !== $command->slug && '0' !== $command->slug ? new TagSlug($command->slug) : null;

        // Create tag through domain service
        $tag = ($this->creator)(
            tagId: $tagId,
            tagName: $tagName,
            slug: $slug,
        );

        // Dispatch domain events
        foreach ($tag->releaseEvents() as $event) {
            $this->messageBus->dispatch($event);
        }
    }
}
