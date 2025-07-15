<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ApproveArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $articleId,
        public string $status,
        public string $reviewerId,
        public \DateTimeImmutable $reviewedAt,
        public string|null $approvalReason = null,
    ) {
    }

    #[\Override]
    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'status' => $this->status,
            'reviewerId' => $this->reviewerId,
            'reviewedAt' => $this->reviewedAt->format(\DateTimeInterface::ATOM),
            'approvalReason' => $this->approvalReason,
        ];
    }
}
