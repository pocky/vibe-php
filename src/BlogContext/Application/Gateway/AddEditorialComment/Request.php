<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AddEditorialComment;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Uid\Uuid;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $articleId,
        public string $reviewerId,
        public string $comment,
        public string|null $selectedText = null,
        public int|null $positionStart = null,
        public int|null $positionEnd = null,
    ) {
    }

    public static function fromData(array $data): self
    {
        if (empty($data['articleId'] ?? '')) {
            throw new \InvalidArgumentException('Article ID is required');
        }

        if (empty($data['reviewerId'] ?? '')) {
            throw new \InvalidArgumentException('Reviewer ID is required');
        }

        if (empty($data['comment'] ?? '')) {
            throw new \InvalidArgumentException('Comment is required');
        }

        $articleId = $data['articleId'];
        if (!Uuid::isValid($articleId)) {
            throw new \InvalidArgumentException('Invalid article ID format');
        }

        $reviewerId = $data['reviewerId'];
        if (!Uuid::isValid($reviewerId)) {
            throw new \InvalidArgumentException('Invalid reviewer ID format');
        }

        // Validate position range if provided
        $positionStart = isset($data['positionStart']) ? (int) $data['positionStart'] : null;
        $positionEnd = isset($data['positionEnd']) ? (int) $data['positionEnd'] : null;

        if (null !== $positionStart && null !== $positionEnd && $positionStart >= $positionEnd) {
            throw new \InvalidArgumentException('Position start must be less than position end');
        }

        return new self(
            articleId: $articleId,
            reviewerId: $reviewerId,
            comment: $data['comment'],
            selectedText: $data['selectedText'] ?? null,
            positionStart: $positionStart,
            positionEnd: $positionEnd,
        );
    }

    public function data(): array
    {
        return array_filter([
            'articleId' => $this->articleId,
            'reviewerId' => $this->reviewerId,
            'comment' => $this->comment,
            'selectedText' => $this->selectedText,
            'positionStart' => $this->positionStart,
            'positionEnd' => $this->positionEnd,
        ], static fn ($value) => null !== $value);
    }
}
