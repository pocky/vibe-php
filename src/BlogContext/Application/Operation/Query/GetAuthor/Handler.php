<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthor;

use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

final readonly class Handler
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        $authorId = new AuthorId($query->authorId);

        $author = $this->repository->findById($authorId);
        if (!$author instanceof \App\BlogContext\Domain\CreateAuthor\Model\Author) {
            throw new \RuntimeException(sprintf('Author with ID "%s" not found', $query->authorId));
        }

        return new View(
            id: $author->id()->getValue(),
            name: $author->name()->getValue(),
            email: $author->email()->getValue(),
            bio: $author->bio()->getValue(),
            createdAt: $author->createdAt(),
            updatedAt: $author->updatedAt()
        );
    }
}
