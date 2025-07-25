<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategory;

final readonly class View
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
