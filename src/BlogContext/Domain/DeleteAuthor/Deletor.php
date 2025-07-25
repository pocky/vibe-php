<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteAuthor;

use App\BlogContext\Domain\DeleteAuthor\Exception\AuthorHasArticles;
use App\BlogContext\Domain\DeleteAuthor\Exception\AuthorNotFound;
use App\BlogContext\Domain\DeleteAuthor\Model\Author;
use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

final readonly class Deletor
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(
        AuthorId $authorId,
        \DateTimeImmutable $deletedAt
    ): Author {
        // Find existing author
        $existingAuthor = $this->repository->findById($authorId);
        if (!$existingAuthor instanceof \App\BlogContext\Domain\CreateAuthor\Model\Author) {
            throw AuthorNotFound::withId($authorId);
        }

        // Check if author has articles
        $articleCount = $this->repository->countArticlesByAuthorId($authorId);
        if (0 < $articleCount) {
            throw AuthorHasArticles::withArticleCount($authorId, $articleCount);
        }

        // Create deleted author model
        return Author::delete($authorId, $deletedAt);
    }
}
