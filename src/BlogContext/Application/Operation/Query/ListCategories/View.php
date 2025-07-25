<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListCategories;

final readonly class View
{
    /**
     * @param array<CategoryView> $categories
     */
    public function __construct(
        public array $categories,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }
}

final readonly class CategoryView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string|null $description,
        public string|null $parentId,
        public int $order,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
