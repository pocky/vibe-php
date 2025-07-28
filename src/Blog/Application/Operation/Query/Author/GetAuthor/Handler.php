<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Author\GetAuthor;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\Repository\AuthorReadRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private AuthorReadRepositoryInterface $authorReadRepository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        $authorId = new AuthorId($query->authorId);

        $author = $this->authorReadRepository->findById($authorId);
        if (!$author instanceof \App\Blog\Application\Shared\ReadModel\AuthorReadModel) {
            throw new \RuntimeException(sprintf('Author with ID "%s" not found', $query->authorId));
        }

        return new View(
            id: $author->id->getValue(),
            name: $author->name->getValue(),
            email: $author->email->getValue(),
            bio: $author->bio->getValue(),
            createdAt: $author->timestamps->getCreatedAt(),
            updatedAt: $author->timestamps->getUpdatedAt()
        );
    }
}
