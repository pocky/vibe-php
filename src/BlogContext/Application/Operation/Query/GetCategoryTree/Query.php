<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategoryTree;

final readonly class Query
{
    public function __construct(
        public string|null $rootId = null,
        public int $maxDepth = 2,
    ) {
        if (1 > $this->maxDepth || 3 < $this->maxDepth) {
            throw new \InvalidArgumentException('Max depth must be between 1 and 3');
        }
    }
}
