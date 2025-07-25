<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetAuthorArticles;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $authorId,
        public int $page = 1,
        public int $limit = 20,
    ) {
        if ('' === $this->authorId) {
            throw new \InvalidArgumentException('Author ID cannot be empty');
        }

        if (1 > $this->page) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if (1 > $this->limit || 100 < $this->limit) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            authorId: $data['authorId'] ?? throw new \InvalidArgumentException('Author ID is required'),
            page: (int) ($data['page'] ?? 1),
            limit: (int) ($data['limit'] ?? 20),
        );
    }

    public function data(): array
    {
        return [
            'authorId' => $this->authorId,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}
