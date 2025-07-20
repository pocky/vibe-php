<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public bool $success,
        public string $message,
        public string|null $articleId = null,
        public string|null $slug = null,
    ) {
    }

    public function data(): array
    {
        $data = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if (null !== $this->articleId) {
            $data['articleId'] = $this->articleId;
        }

        if (null !== $this->slug) {
            $data['slug'] = $this->slug;
        }

        return $data;
    }
}
