<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteAuthor;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public bool $deleted = true,
    ) {
    }

    public function data(): array
    {
        return [
            'deleted' => $this->deleted,
        ];
    }
}
