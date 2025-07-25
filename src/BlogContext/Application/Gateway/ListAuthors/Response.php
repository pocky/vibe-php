<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListAuthors;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    /**
     * @param array<array{
     *     id: string,
     *     name: string,
     *     email: string,
     *     bio: string,
     *     createdAt: string,
     *     updatedAt: string
     * }> $authors
     */
    public function __construct(
        public array $authors,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function data(): array
    {
        return [
            'authors' => $this->authors,
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}
