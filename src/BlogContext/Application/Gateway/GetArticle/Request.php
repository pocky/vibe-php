<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $id,
    ) {
        if ('' === $this->id) {
            throw new \InvalidArgumentException('Article ID cannot be empty');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            id: $data['id'] ?? throw new \InvalidArgumentException('Article ID is required'),
        );
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
