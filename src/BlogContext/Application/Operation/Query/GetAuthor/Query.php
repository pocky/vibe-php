<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthor;

final readonly class Query
{
    public function __construct(
        public string $authorId,
    ) {
        if ('' === trim($authorId)) {
            throw new \InvalidArgumentException('Author ID cannot be empty');
        }
    }
}
