<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\SubmitForReview;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Uid\Uuid;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $articleId,
        public string|null $authorId = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        if (empty($data['articleId'] ?? '')) {
            throw new \InvalidArgumentException('Article ID is required');
        }

        $articleId = $data['articleId'];
        if (!Uuid::isValid($articleId)) {
            throw new \InvalidArgumentException('Invalid article ID format');
        }

        $authorId = $data['authorId'] ?? null;
        if (null !== $authorId && !Uuid::isValid($authorId)) {
            throw new \InvalidArgumentException('Invalid author ID format');
        }

        return new self(
            articleId: $articleId,
            authorId: $authorId,
        );
    }

    #[\Override]
    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'authorId' => $this->authorId,
        ];
    }
}
