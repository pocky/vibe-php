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
    ) {
    }

    public function toArray(): array
    {
        return [
            'categories' => array_map(
                fn (CategoryView $category) => $category->toArray(),
                $this->categories
            ),
            'total' => $this->total,
        ];
    }
}

final readonly class CategoryView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $path,
        public string|null $parentId,
        public int $level,
        public int $articleCount,
        public string $createdAt,
        public string|null $updatedAt = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'path' => $this->path,
            'parentId' => $this->parentId,
            'level' => $this->level,
            'articleCount' => $this->articleCount,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
