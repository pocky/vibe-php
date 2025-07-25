<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetAuthor;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public array $author,
    ) {
    }

    public function data(): array
    {
        return [
            'author' => $this->author,
        ];
    }
}
