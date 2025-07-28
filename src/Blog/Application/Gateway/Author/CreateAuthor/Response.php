<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Author\CreateAuthor;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $authorId,
        public bool $success = true,
    ) {
    }

    public function data(): array
    {
        return [
            'authorId' => $this->authorId,
            'success' => $this->success,
        ];
    }
}
