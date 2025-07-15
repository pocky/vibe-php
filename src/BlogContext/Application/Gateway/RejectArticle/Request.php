<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\RejectArticle;

use App\Shared\Application\Gateway\GatewayRequest;
use Symfony\Component\Uid\Uuid;

final readonly class Request implements GatewayRequest
{
    private const int MAX_REASON_LENGTH = 1000;

    public function __construct(
        public string $articleId,
        public string $reviewerId,
        public string $reason,
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

        // Check if reason was provided at all
        if (!isset($data['reason'])) {
            throw new \InvalidArgumentException('Rejection reason is required');
        }

        $articleId = $data['articleId'];
        if (!Uuid::isValid($articleId)) {
            throw new \InvalidArgumentException('Invalid article ID format');
        }

        $reviewerId = $data['reviewerId'];
        if (!Uuid::isValid($reviewerId)) {
            throw new \InvalidArgumentException('Invalid reviewer ID format');
        }

        $reason = $data['reason'];
        $trimmedReason = trim($reason);
        if ('' === $trimmedReason) {
            throw new \InvalidArgumentException('Rejection reason cannot be empty');
        }

        if (self::MAX_REASON_LENGTH < mb_strlen($reason)) {
            throw new \InvalidArgumentException(sprintf('Rejection reason cannot exceed %d characters', self::MAX_REASON_LENGTH));
        }

        return new self(
            articleId: $articleId,
            reviewerId: $reviewerId,
            reason: $reason,
        );
    }

    #[\Override]
    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'reviewerId' => $this->reviewerId,
            'reason' => $this->reason,
        ];
    }
}
