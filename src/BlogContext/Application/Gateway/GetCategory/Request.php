<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategory;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $categoryId,
    ) {
        if ('' === $categoryId || '0' === $categoryId) {
            throw new \InvalidArgumentException('Category ID cannot be empty');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            categoryId: $data['categoryId'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            'categoryId' => $this->categoryId,
        ];
    }
}
