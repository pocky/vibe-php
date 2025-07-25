<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\DeleteAuthor;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $authorId,
    ) {
        if ('' === trim($this->authorId)) {
            throw new \InvalidArgumentException('Author ID is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            authorId: $data['authorId'] ?? '',
        );
    }

    public function data(): array
    {
        return [
            'authorId' => $this->authorId,
        ];
    }
}
