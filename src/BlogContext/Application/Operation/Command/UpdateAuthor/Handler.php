<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateAuthor;

use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\BlogContext\Domain\UpdateAuthor\Updater;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private Updater $updater,
        private AuthorRepositoryInterface $repository,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $authorId = new AuthorId($command->authorId);
        $name = new AuthorName($command->name);
        $email = new AuthorEmail($command->email);
        $bio = new AuthorBio($command->bio);
        $updatedAt = new \DateTimeImmutable();

        // Update author through domain service
        $author = ($this->updater)($authorId, $name, $email, $bio, $updatedAt);

        // Persist updated author
        $this->repository->update($author);

        // Dispatch domain events
        foreach ($author->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
