<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AutoSaveArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Article ID is required')]
        #[Assert\Uuid(message: 'Article ID must be a valid UUID')]
        public string $articleId,
        #[Assert\Length(
            min: 1,
            max: 200,
            minMessage: 'Article title must be at least {{ limit }} character',
            maxMessage: 'Article title cannot exceed {{ limit }} characters'
        )]
        public string $title,
        #[Assert\Length(
            min: 0,
            minMessage: 'Article content cannot be negative'
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
            title: $data['title'] ?? '',
            content: $data['content'] ?? '',
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
