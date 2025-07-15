<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListArticles;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public array $articles,
        public int $total,
        public int $page,
        public int $limit,
        public bool $hasNextPage,
    ) {
    }

    public function data(): array
    {
        // Return data directly without nested structure
        return [
            'articles' => $this->articles,
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
            'has_next_page' => $this->hasNextPage,
        ];
    }

    public function getArticles(): array
    {
        return $this->articles;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function hasNextPage(): bool
    {
        return $this->hasNextPage;
    }
}
