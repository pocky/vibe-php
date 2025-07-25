<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateCategory;

final readonly class Command
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string|null $description = null,
        public string|null $parentId = null,
        public int $order = 0,
    ) {
    }
}
