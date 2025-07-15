<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AutoSaveArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $content,
        public string $slug,
        public string $status,
        public \DateTimeImmutable $autoSavedAt,
    ) {
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'status' => $this->status,
            'autoSavedAt' => $this->autoSavedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
