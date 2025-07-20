<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public string|null $slug = null,
    ) {
    }

    public function data(): array
    {
        $data = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if (null !== $this->slug) {
            $data['slug'] = $this->slug;
        }

        return $data;
    }
}
