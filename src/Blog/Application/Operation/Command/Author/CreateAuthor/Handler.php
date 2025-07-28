<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Author\CreateAuthor;

use App\Blog\Domain\Author\AuthorCreator;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private AuthorCreator $creator,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $authorName = new AuthorName($command->name);
        $authorEmail = new AuthorEmail($command->email);
        $authorBio = new AuthorBio($command->bio);

        // Create author through domain service passing the ID from command
        $author = ($this->creator)($command->authorId, $authorName, $authorEmail, $authorBio);

        // Dispatch domain events
        foreach ($author->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
