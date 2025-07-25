<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateCategory;

final readonly class Command
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string $description,
        public string|null $parentCategoryId = null,
        public int $order = 0,
    ) {
    }
}
