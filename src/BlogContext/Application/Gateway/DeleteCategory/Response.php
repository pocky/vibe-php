<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteCategory;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $categoryId,
        public \DateTimeImmutable $deletedAt,
    ) {
    }

    public function data(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'deletedAt' => $this->deletedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
