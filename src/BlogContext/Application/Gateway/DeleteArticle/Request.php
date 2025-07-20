<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteArticle;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $articleId,
        public string $deletedBy,
    ) {
        if ('' === trim($this->articleId)) {
            throw new \InvalidArgumentException('Article ID is required');
        }

        if ('' === trim($this->deletedBy)) {
            throw new \InvalidArgumentException('DeletedBy is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            articleId: $data['articleId'] ?? '',
            deletedBy: $data['deletedBy'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'deletedBy' => $this->deletedBy,
        ];
    }
}
