<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticles;

interface ListerInterface
{
    public function __invoke(ListCriteria $criteria): ArticlesListData;
}
