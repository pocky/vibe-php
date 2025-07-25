<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListAuthors;

use App\BlogContext\Domain\Shared\Repository\AuthorRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        // Calculate offset from page and limit
        $offset = ($query->page - 1) * $query->limit;

        // Fetch authors
        $authors = $this->repository->findAllPaginated($query->limit, $offset);

        // Get total count
        $total = $this->repository->countAll();

        // Transform to view models
        $authorViews = array_map(
            fn ($author) => new AuthorView(
                id: $author->id()->getValue(),
                name: $author->name()->getValue(),
                email: $author->email()->getValue(),
                bio: $author->bio()->getValue(),
                createdAt: $author->createdAt(),
                updatedAt: $author->updatedAt()
            ),
            $authors
        );

        return new View(
            authors: $authorViews,
            total: $total,
            page: $query->page,
            limit: $query->limit
        );
    }
}
