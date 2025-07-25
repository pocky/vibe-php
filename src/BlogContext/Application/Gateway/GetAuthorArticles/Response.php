<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetAuthorArticles;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    /**
     * @param array<array{
     *     id: string,
     *     title: string,
     *     slug: string,
     *     status: string,
     *     publishedAt: string|null
     * }> $articles
     */
    public function __construct(
        public array $articles,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function data(): array
    {
        return [
            'articles' => $this->articles,
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}
