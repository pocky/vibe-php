<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Repository\AuthorWriteRepositoryInterface;

final readonly class AuthorDeletor
{
    public function __construct(
        private AuthorWriteRepositoryInterface $authorRepository,
    ) {
    }

    public function __invoke(AuthorId $authorId): void
    {
        // Note: This is a simplified implementation
        // In a real scenario, you might want to check if author has articles
        // and prevent deletion or cascade delete

        // For now, we'll just attempt to remove by ID
        // The repository implementation should handle the "not found" case
        $this->authorRepository->removeById($authorId);
    }
}
