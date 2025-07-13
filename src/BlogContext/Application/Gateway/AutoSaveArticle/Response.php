<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\AutoSaveArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $articleId,
        public string $status,
        public \DateTimeImmutable $autoSavedAt,
        public bool $hasChanges = true,
    ) {
    }

    public function data(): array
    {
        return [
            'articleId' => $this->articleId,
            'status' => $this->status,
            'autoSavedAt' => $this->autoSavedAt->format(\DateTimeInterface::ATOM),
            'hasChanges' => $this->hasChanges,
        ];
    }
}
