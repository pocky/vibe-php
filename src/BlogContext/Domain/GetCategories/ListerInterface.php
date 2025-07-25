<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategories;

interface ListerInterface
{
    public function __invoke(ListCriteria $criteria): CategoriesListData;
}
