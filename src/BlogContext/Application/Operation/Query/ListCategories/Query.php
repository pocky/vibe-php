<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListCategories;

final readonly class Query
{
    public function __construct(
        // Collection query parameters
        public int $page = 1,
        public int $limit = 20,
        public string|null $sortBy = null,
        public string|null $sortOrder = 'asc',
        // TODO: Add filter parameters
        // public ?string $status = null,
        // public ?string $search = null,
    ) {
        if (1 > $this->page) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if (1 > $this->limit || 100 < $this->limit) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }

        if (null !== $this->sortOrder && !in_array($this->sortOrder, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException('Sort order must be "asc" or "desc"');
        }
    }
}
