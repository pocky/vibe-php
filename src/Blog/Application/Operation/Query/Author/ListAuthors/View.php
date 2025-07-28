<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Author\ListAuthors;

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
