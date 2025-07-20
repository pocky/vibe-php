<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListArticles;

use App\BlogContext\Domain\GetArticles\ArticlesListData;
use App\BlogContext\Domain\GetArticles\ListCriteria;
use App\BlogContext\Domain\GetArticles\ListerInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private ListerInterface $lister,
    ) {
    }

    public function __invoke(Query $query): ArticlesListData
    {
        $criteria = new ListCriteria(
            status: $query->status ? ArticleStatus::from($query->status) : null,
            authorId: $query->authorId,
            page: $query->page,
            limit: $query->limit,
            sortBy: $query->sortBy ?? 'createdAt',
            sortOrder: strtoupper($query->sortOrder ?? 'DESC'),
        );

        return ($this->lister)($criteria);
    }
}
