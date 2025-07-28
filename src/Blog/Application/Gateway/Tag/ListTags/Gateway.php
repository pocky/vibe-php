<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\ListTags;

use App\Blog\Domain\Tag\Shared\Repository\TagReadRepositoryInterface;

final readonly class Gateway
{
    public function __construct(
        private TagReadRepositoryInterface $tagReadRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        // Calculate offset from page and itemsPerPage
        $limit = $request->itemsPerPage;
        $page = $request->page;
        $offset = ($page - 1) * $limit;

        // Get paginated tags
        $result = $this->tagReadRepository->findPaginated(
            limit: $limit,
            offset: $offset
        );

        return new Response(
            tags: $result['tags'] ?? [],
            total: $result['total'] ?? 0,
        );
    }
}
