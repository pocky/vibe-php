<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(
            min: 5,
            max: 200,
            minMessage: 'Article title must be at least {{ limit }} characters',
            maxMessage: 'Article title cannot exceed {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[^<>"\']*$/',
            message: 'Article title contains invalid characters'
        )]
        public readonly string $title,
        #[Assert\NotBlank(message: 'Content is required')]
        #[Assert\Length(
            min: 10,
            minMessage: 'Article content must be at least {{ limit }} characters'
        )]
        public readonly string $content,
        #[Assert\NotBlank(message: 'Slug is required')]
        #[Assert\Length(
            min: 3,
            max: 250,
            minMessage: 'Article slug must be at least {{ limit }} characters',
            maxMessage: 'Article slug cannot exceed {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-z0-9-]+$/',
            message: 'Slug must contain only lowercase letters, numbers and hyphens'
        )]
        public readonly string $slug,
        #[Assert\Choice(
            choices: ['draft', 'published', 'archived'],
            message: 'Status must be one of: draft, published, archived'
        )]
        public readonly string $status,
        #[Assert\NotNull(message: 'Creation date is required')]
        public readonly \DateTimeImmutable $createdAt,
        #[Assert\Uuid(message: 'Invalid author ID format')]
        public readonly string|null $authorId = null,
    ) {
    }

    #[Assert\IsTrue(message: 'Author ID must be a valid UUID when provided')]
    public function isValidAuthorId(): bool
    {
        return null === $this->authorId || Uuid::isValid($this->authorId);
    }

    #[Assert\IsTrue(message: 'Status must be a valid ArticleStatus')]
    public function isValidStatus(): bool
    {
        return in_array($this->status, ['draft', 'published', 'archived'], true);
    }

    #[Assert\IsTrue(message: 'Slug must be unique and properly formatted')]
    public function isValidSlug(): bool
    {
        return 1 === preg_match('/^[a-z0-9-]+$/', $this->slug);
    }

    public static function fromData(array $data): self
    {
        return new self(
            title: $data['title'] ?? throw new \InvalidArgumentException('Title is required'),
            content: $data['content'] ?? throw new \InvalidArgumentException('Content is required'),
            slug: $data['slug'] ?? throw new \InvalidArgumentException('Slug is required'),
            status: $data['status'] ?? 'draft',
            createdAt: isset($data['createdAt'])
                ? new \DateTimeImmutable($data['createdAt'])
                : new \DateTimeImmutable(),
            authorId: $data['authorId'] ?? null,
        );
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'slug' => $this->slug,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'authorId' => $this->authorId,
        ];
    }
}
