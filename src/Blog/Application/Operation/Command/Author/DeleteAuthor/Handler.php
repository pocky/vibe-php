<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Author\DeleteAuthor;

use App\Blog\Domain\Author\AuthorDeletor;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;

final readonly class Handler
{
    public function __construct(
        private AuthorDeletor $deletor,
    ) {
    }

    public function __invoke(Command $command): void
    {
        // Transform command data to value objects
        $authorId = new AuthorId($command->authorId);

        // Delete author through domain service (physical deletion)
        ($this->deletor)($authorId);

        // Note: No domain events to dispatch for physical deletion
        // The repository handles the removal directly
    }
}
