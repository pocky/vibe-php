<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\PublishArticle;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $articleId,
        public string|null $publishAt = null,
    ) {
        if ('' === trim($this->articleId)) {
            throw new \InvalidArgumentException('Article ID is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            articleId: $data['articleId'] ?? '',
            publishAt: $data['publishAt'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'publishAt' => $this->publishAt,
        ];
    }
}
