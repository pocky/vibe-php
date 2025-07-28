<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Article\CreateArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: 'Title must be at least {{ limit }} characters long',
            maxMessage: 'Title cannot be longer than {{ limit }} characters'
        )]
        public string $title,
        #[Assert\NotBlank(message: 'Content is required')]
        #[Assert\Length(
            min: 10,
            minMessage: 'Content must be at least {{ limit }} characters long'
        )]
        public string $content,
        #[Assert\NotBlank(message: 'Author ID is required')]
        #[Assert\Uuid(message: 'Author ID must be a valid UUID')]
        public string $authorId,
        #[Assert\Length(
            max: 255,
            maxMessage: 'Slug cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            message: 'Slug must contain only lowercase letters, numbers, and hyphens'
        )]
        public string|null $slug = null,
        #[Assert\Choice(
            choices: ['draft', 'published'],
            message: 'Status must be either "draft" or "published"'
        )]
        public string $status = 'draft',
    ) {
    }

    public static function fromData(array $data): self
    {
        if (!isset($data['title'])) {
            throw new \InvalidArgumentException('Title is required');
        }

        if (!isset($data['content'])) {
            throw new \InvalidArgumentException('Content is required');
        }

        if (!isset($data['authorId'])) {
            throw new \InvalidArgumentException('Author ID is required');
        }

        return new self(
            title: $data['title'],
            content: $data['content'],
            authorId: $data['authorId'],
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
