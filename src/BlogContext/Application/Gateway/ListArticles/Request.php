<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ListArticles;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
        public string|null $status = null,
        public string|null $search = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 20;
        $status = $data['status'] ?? null;
        $search = $data['search'] ?? null;

        if (0 >= $page) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if (1 > $limit || 100 < $limit) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }

        return new self($page, $limit, $status, $search);
    }

    public function data(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'status' => $this->status,
            'search' => $this->search,
        ];
    }
}
