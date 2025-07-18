<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateCategory;

final readonly class Command
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string|null $parentId = null,
        public \DateTimeImmutable|null $createdAt = null,
    ) {
        if ('' === $this->categoryId) {
            throw new \InvalidArgumentException('Category ID cannot be empty');
        }

        if ('' === $this->name) {
            throw new \InvalidArgumentException('Category name cannot be empty');
        }

        if ('' === $this->slug) {
            throw new \InvalidArgumentException('Category slug cannot be empty');
        }
    }
}
