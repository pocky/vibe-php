<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $articleId,
        public string $status,
        public \DateTimeImmutable $publishedAt,
    ) {
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'status' => $this->status,
            'publishedAt' => $this->publishedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
