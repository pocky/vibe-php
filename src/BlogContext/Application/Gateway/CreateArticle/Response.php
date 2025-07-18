<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $articleId,
        public string $slug,
        public string $status,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'slug' => $this->slug,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
