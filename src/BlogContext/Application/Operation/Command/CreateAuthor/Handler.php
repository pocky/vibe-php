<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateAuthor;

use App\BlogContext\Domain\CreateAuthor\Creator;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private Creator $creator,
        private AuthorRepositoryInterface $repository,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $name = new AuthorName($command->name);
        $email = new AuthorEmail($command->email);
        $bio = new AuthorBio($command->bio);
        $createdAt = new \DateTimeImmutable();

        // Create author through domain service
        $author = ($this->creator)($name, $email, $bio, $createdAt);

        // Persist author
        $this->repository->add($author);

        // Dispatch domain events
        foreach ($author->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
