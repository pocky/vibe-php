<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategoryTree;

final readonly class View
{
    /**
     * @param array<CategoryNode> $tree
     */
    public function __construct(
        public array $tree,
    ) {
    }
}

final readonly class CategoryNode
{
    /**
     * @param array<CategoryNode> $children
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string|null $description,
        public int $order,
        public array $children = [],
    ) {
    }
}
