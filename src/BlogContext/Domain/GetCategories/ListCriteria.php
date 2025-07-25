<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategories;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final readonly class ListCriteria
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public string $sortBy = 'order',
        public string $sortOrder = 'ASC',
        public CategoryId|null $parentId = null,
    ) {
        if (1 > $this->page) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if (1 > $this->limit || 100 < $this->limit) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }

        if (!in_array($this->sortOrder, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException('Sort order must be ASC or DESC');
        }
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
