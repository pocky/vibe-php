<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public string $slug,
        public string $status,
        public string $authorId,
        public string $createdAt,
        public string $updatedAt,
        public string|null $publishedAt = null,
    ) {
    }

    public function data(): array
    {
        return [
            'article' => [
                'id' => $this->id,
                'title' => $this->title,
                'content' => $this->content,
                'slug' => $this->slug,
                'status' => $this->status,
                'authorId' => $this->authorId,
                'createdAt' => $this->createdAt,
                'updatedAt' => $this->updatedAt,
                'publishedAt' => $this->publishedAt,
            ],
        ];
    }
}
