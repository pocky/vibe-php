<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListArticles;

use App\BlogContext\Domain\GetArticles\ArticlesListData;

interface HandlerInterface
{
    public function __invoke(Query $query): ArticlesListData;
}
