<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Article\GetArticle;

final readonly class Query
{
    public function __construct(
        // Single item query parameters
        public string $id,
        // TODO: Add other query parameters if needed
    ) {
    }
}
