<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Article ID is required')]
        #[Assert\Uuid(message: 'Article ID must be a valid UUID')]
        public string $articleId,
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(
            min: 5,
            max: 200,
            minMessage: 'Article title must be at least {{ limit }} characters',
            maxMessage: 'Article title cannot exceed {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[^<>"\'].*$/',
            message: 'Article title contains invalid characters'
        )]
        public string $title,
        #[Assert\NotBlank(message: 'Content is required')]
        #[Assert\Length(
            min: 10,
            minMessage: 'Article content must be at least {{ limit }} characters'
        )]
        public string $content,
    ) {
    }

    #[Assert\IsTrue(message: 'Article ID must be a valid UUID')]
    public function isValidArticleId(): bool
    {
        return Uuid::isValid($this->articleId);
    }

    public static function fromData(array $data): self
    {
        return new self(
            articleId: $data['articleId'] ?? throw new \InvalidArgumentException('Article ID is required'),
            title: $data['title'] ?? throw new \InvalidArgumentException('Title is required'),
            content: $data['content'] ?? throw new \InvalidArgumentException('Content is required'),
        );
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}
