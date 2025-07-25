<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategoryTree;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    /**
     * @param array<array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     order: int,
     *     children: array
     * }> $tree
     */
    public function __construct(
        public array $tree,
    ) {
    }

    public function data(): array
    {
        return [
            'tree' => $this->tree,
        ];
    }
}
