<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListCategories;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    /**
     * @param array<array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     parentId: string|null,
     *     order: int,
     *     createdAt: string,
     *     updatedAt: string
     * }> $categories
     */
    public function __construct(
        public array $categories,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function data(): array
    {
        return [
            'categories' => $this->categories,
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}
