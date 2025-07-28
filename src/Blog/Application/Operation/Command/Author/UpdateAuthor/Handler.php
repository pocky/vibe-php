<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Author\UpdateAuthor;

use App\Blog\Domain\Author\AuthorUpdater;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private AuthorUpdater $updater,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $authorId = new AuthorId($command->authorId);
        $authorName = new AuthorName($command->name);
        $authorEmail = new AuthorEmail($command->email);
        $authorBio = new AuthorBio($command->bio);
        // Update author through domain service
        $author = ($this->updater)($authorId, $authorName, $authorEmail, $authorBio);

        // Dispatch domain events
        foreach ($author->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
