<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategory;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public array $category,
    ) {
    }

    public function data(): array
    {
        return [
            'category' => $this->category,
        ];
    }
}
