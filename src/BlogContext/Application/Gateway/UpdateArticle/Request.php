<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $articleId,
        public string|null $title = null,
        public string|null $content = null,
        public string|null $slug = null,
    ) {
        if ('' === trim($this->articleId)) {
            throw new \InvalidArgumentException('Article ID is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            articleId: $data['articleId'] ?? '',
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            slug: $data['slug'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
        ];
    }
}
