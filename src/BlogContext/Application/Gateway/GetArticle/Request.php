<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $id,
    ) {
    }

    public static function fromData(array $data): self
    {
        if (empty($data['id'] ?? '')) {
            throw new \InvalidArgumentException('Article ID is required');
        }

        return new self($data['id']);
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
