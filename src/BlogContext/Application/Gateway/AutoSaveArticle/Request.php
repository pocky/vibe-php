<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AutoSaveArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Article ID is required')]
        #[Assert\Uuid(message: 'Article ID must be a valid UUID')]
        public string $articleId,
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(min: 5, max: 200)]
        public string $title,
        #[Assert\NotBlank(message: 'Content is required')]
        #[Assert\Length(min: 10)]
        public string $content,
    ) {
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
