<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategories;

use App\BlogContext\Domain\GetCategories\Model\Category;

final readonly class CategoriesListData
{
    /**
     * @param Category[] $categories
     */
    public function __construct(
        public array $categories,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function hasMore(): bool
    {
        return ($this->page * $this->limit) < $this->total;
    }

    public function totalPages(): int
    {
        return (int) ceil($this->total / $this->limit);
    }
}
