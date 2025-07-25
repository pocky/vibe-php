<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\DeleteAuthor;

use App\BlogContext\Domain\DeleteAuthor\Deletor;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;

final readonly class Handler
{
    public function __construct(
        private Deletor $deletor,
        private AuthorRepositoryInterface $repository,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $authorId = new AuthorId($command->authorId);
        $deletedAt = new \DateTimeImmutable();

        // Delete author through domain service
        $author = ($this->deletor)($authorId, $deletedAt);

        // Remove author from repository
        $this->repository->remove($author);

        // Dispatch domain events
        foreach ($author->releaseEvents() as $event) {
            ($this->eventBus)($event);
        }
    }
}
