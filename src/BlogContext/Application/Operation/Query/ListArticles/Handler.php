<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListArticles;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class Handler
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(Query $query): array
    {
        $filters = [];
        if (null !== $query->status) {
            $filters['status'] = $query->status;
        }

        $paginator = $this->repository->findAllPaginated(
            $query->page,
            $query->limit,
            $filters
        );

        return [
            'articles' => $paginator->getItems(),
            'total' => $paginator->getTotalItems(),
            'page' => $paginator->getCurrentPage(),
            'limit' => $paginator->getItemsPerPage(),
            'hasNextPage' => $paginator->hasNextPage(),
        ];
    }
}
