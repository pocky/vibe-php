<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AddEditorialComment;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $id,
        public string $articleId,
        public string $reviewerId,
        public string $comment,
        public \DateTimeImmutable $createdAt,
        public string|null $selectedText = null,
        public int|null $positionStart = null,
        public int|null $positionEnd = null,
    ) {
    }

    public function data(): array
    {
        return array_filter([
            'id' => $this->id,
            'articleId' => $this->articleId,
            'reviewerId' => $this->reviewerId,
            'comment' => $this->comment,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'selectedText' => $this->selectedText,
            'positionStart' => $this->positionStart,
            'positionEnd' => $this->positionEnd,
        ], static fn ($value) => null !== $value);
    }
}
