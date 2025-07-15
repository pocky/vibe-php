<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListArticles;

final readonly class Query
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public string|null $status = null,
    ) {
    }
}
