<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Category\GetCategory;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;

final readonly class View
{
    public function __construct(
        public CategoryReadModel $category,
    ) {
    }
}
