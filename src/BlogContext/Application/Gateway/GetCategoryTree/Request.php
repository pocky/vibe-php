<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategoryTree;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string|null $rootId = null,
        public int $maxDepth = 2,
    ) {
        if (1 > $this->maxDepth || 3 < $this->maxDepth) {
            throw new \InvalidArgumentException('Max depth must be between 1 and 3');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            rootId: $data['rootId'] ?? null,
            maxDepth: (int) ($data['maxDepth'] ?? 2),
        );
    }

    public function data(): array
    {
        return [
            'rootId' => $this->rootId,
            'maxDepth' => $this->maxDepth,
        ];
    }
}
