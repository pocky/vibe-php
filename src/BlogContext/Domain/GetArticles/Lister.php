<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticles;

use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;

final readonly class Lister implements ListerInterface
{
    public function __construct(
        private ArticleRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ListCriteria $criteria): ArticlesListData
    {
        // Get articles from repository with criteria
        $result = $this->repository->findByCriteria(
            status: $criteria->status,
            authorId: $criteria->authorId,
            limit: $criteria->limit,
            offset: $criteria->getOffset(),
            sortBy: $criteria->sortBy,
            sortOrder: $criteria->sortOrder,
        );

        // Transform read models to DTOs
        $articles = array_map(
            static fn ($readModel) => Model\Article::fromReadModel($readModel),
            $result['articles']
        );

        // Create list data with pagination info
        return ArticlesListData::create(
            articles: $articles,
            total: $result['total'],
            page: $criteria->page,
            limit: $criteria->limit,
        );
    }
}
