<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\UpdateArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Article ID is required')]
        #[Assert\Uuid(message: 'Article ID must be a valid UUID')]
        public string $articleId,
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: 'Title must be at least {{ limit }} characters long',
            maxMessage: 'Title cannot be longer than {{ limit }} characters'
        )]
        public string|null $title = null,
        #[Assert\Length(
            min: 10,
            minMessage: 'Content must be at least {{ limit }} characters long'
        )]
        public string|null $content = null,
        #[Assert\Length(
            max: 255,
            maxMessage: 'Slug cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            message: 'Slug must contain only lowercase letters, numbers, and hyphens'
        )]
        public string|null $slug = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        return new self(
            articleId: $data['articleId'],
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
