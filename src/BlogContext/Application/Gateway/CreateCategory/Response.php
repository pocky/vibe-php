<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateCategory;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public string|null $categoryId = null,
        public string|null $slug = null,
    ) {
    }

    public function data(): array
    {
        $data = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if (null !== $this->categoryId) {
            $data['categoryId'] = $this->categoryId;
        }

        if (null !== $this->slug) {
            $data['slug'] = $this->slug;
        }

        return $data;
    }
}
