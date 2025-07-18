<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListCategories;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
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
