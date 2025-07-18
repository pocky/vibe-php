<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategory;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $id,
    ) {
        if ('' === $this->id) {
            throw new \InvalidArgumentException('Category ID cannot be empty');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
