<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Tag\SearchTags;

use App\Blog\Domain\Tag\Shared\Repository\TagReadRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private TagReadRepositoryInterface $tagReadRepository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        $offset = ($query->page - 1) * $query->limit;

        if (null !== $query->searchTerm && '' !== $query->searchTerm && '0' !== $query->searchTerm) {
            // Search by name pattern
            $tags = $this->tagReadRepository->findByNamePattern($query->searchTerm, $query->limit);
            $total = count($tags); // For simplicity, actual count would need a separate method
        } else {
            // Get paginated tags
            $result = $this->tagReadRepository->findPaginated($query->limit, $offset);
            $tags = $result['tags'];
            $total = $result['total'];
        }

        return new View(
            items: array_map(
                fn ($tag): TagView => new TagView(
                    id: $tag->id->getValue(),
                    name: $tag->name->getValue(),
                    slug: $tag->slug->getValue(),
                    createdAt: $tag->createdAt()->format(\DateTimeInterface::ATOM),
                    updatedAt: $tag->updatedAt()->format(\DateTimeInterface::ATOM),
                ),
                $tags
            ),
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
