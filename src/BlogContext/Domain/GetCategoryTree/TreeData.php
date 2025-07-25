<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategoryTree;

final readonly class TreeData
{
    /**
     * @param array<array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     order: int,
     *     children: array
     * }> $nodes
     */
    public function __construct(
        public array $nodes,
    ) {
    }
}
