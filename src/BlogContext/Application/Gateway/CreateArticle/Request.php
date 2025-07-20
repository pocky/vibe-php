<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $title,
        public string $content,
        public string $authorId,
        public string|null $slug = null,
        public string $status = 'draft',
    ) {
        if ('' === trim($this->title)) {
            throw new \InvalidArgumentException('Title is required');
        }

        if ('' === trim($this->content)) {
            throw new \InvalidArgumentException('Content is required');
        }

        if ('' === trim($this->authorId)) {
            throw new \InvalidArgumentException('Author ID is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            content: $data['content'] ?? '',
            authorId: $data['authorId'] ?? '',
            slug: $data['slug'] ?? null,
            status: $data['status'] ?? 'draft',
        );
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'authorId' => $this->authorId,
            'slug' => $this->slug,
            'status' => $this->status,
        ];
    }
}
