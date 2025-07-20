<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public bool $success,
        public string $message,
    ) {
    }

    public function data(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
