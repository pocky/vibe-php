<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\SubmitForReview;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $articleId,
        public string $status,
        public \DateTimeImmutable $submittedAt,
    ) {
    }

    #[\Override]
    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'status' => $this->status,
            'submittedAt' => $this->submittedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
