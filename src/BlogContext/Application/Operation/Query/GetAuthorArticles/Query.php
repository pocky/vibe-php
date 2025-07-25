<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthorArticles;

final readonly class Query
{
    public function __construct(
        public string $authorId,
        public int $page = 1,
        public int $limit = 20,
    ) {
        if ('' === trim($authorId)) {
            throw new \InvalidArgumentException('Author ID cannot be empty');
        }

        if (1 > $page) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if (1 > $limit || 100 < $limit) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
    }
}
