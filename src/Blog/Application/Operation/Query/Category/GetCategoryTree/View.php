<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Category\GetCategoryTree;

final readonly class View
{
    /**
     * @param array<array{category: \App\Blog\Application\Shared\ReadModel\CategoryReadModel, children: array}> $tree
     */
    public function __construct(
        public array $tree,
    ) {
    }
}

final readonly class CategoryNodeView
{
    /**
     * @param array<CategoryNodeView> $children
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $description,
        public string|null $parentId,
        public int $order,
        public string $createdAt,
        public string $updatedAt,
        public array $children,
    ) {
    }
}
