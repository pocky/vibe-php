<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListAuthors;

final readonly class View
{
    /**
     * @param AuthorView[] $authors
     */
    public function __construct(
        public array $authors,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }
}
