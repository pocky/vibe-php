<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Author\ListAuthors;

use App\Blog\Domain\Author\Shared\Repository\AuthorReadRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private AuthorReadRepositoryInterface $authorReadRepository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        // Calculate offset from page and limit
        $offset = ($query->page - 1) * $query->limit;

        // Fetch authors with pagination
        $result = $this->authorReadRepository->findAllPaginated($query->limit, $offset);

        // Transform to view models
        $authorViews = array_map(
            fn ($author): AuthorView => new AuthorView(
                id: $author->id->getValue(),
                name: $author->name->getValue(),
                email: $author->email->getValue(),
                bio: $author->bio->getValue(),
                createdAt: $author->timestamps->getCreatedAt(),
                updatedAt: $author->timestamps->getUpdatedAt()
            ),
            $result['authors']
        );

        return new View(
            authors: $authorViews,
            total: $result['total'],
            page: $query->page,
            limit: $query->limit
        );
    }
}
