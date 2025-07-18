<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategory;

final readonly class View
{
    /**
     * @param array<CategoryView> $children
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $path,
        public string|null $parentId,
        public int $level,
        public int $articleCount,
        public array $children,
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
            'children' => array_map(
                fn (CategoryView $child) => $child->toArray(),
                $this->children
            ),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
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
        public int $level,
        public int $articleCount,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'path' => $this->path,
            'level' => $this->level,
            'articleCount' => $this->articleCount,
        ];
    }
}
