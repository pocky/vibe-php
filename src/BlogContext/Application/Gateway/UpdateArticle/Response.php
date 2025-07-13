<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $slug,
        public string $status,
        public \DateTimeImmutable $updatedAt,
        public array $changedFields = [],
    ) {
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
            'changedFields' => $this->changedFields,
        ];
    }
}
