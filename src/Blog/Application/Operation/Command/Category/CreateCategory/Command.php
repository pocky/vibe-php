<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Category\CreateCategory;

final readonly class Command
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string $description,
        public string|null $parentId = null,
        public int|null $order = null,
    ) {
        if ('' === trim($this->categoryId)) {
            throw new \InvalidArgumentException('Category ID cannot be empty');
        }

        if ('' === trim($this->name)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        if ('' === trim($this->slug)) {
            throw new \InvalidArgumentException('Slug cannot be empty');
        }

        if ('' === trim($this->description)) {
            throw new \InvalidArgumentException('Description cannot be empty');
        }

        if (null !== $this->parentId && '' === trim($this->parentId)) {
            throw new \InvalidArgumentException('Parent ID cannot be empty string');
        }

        if (null !== $this->order && 0 > $this->order) {
            throw new \InvalidArgumentException('Order must be non-negative');
        }
    }
}
