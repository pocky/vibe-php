<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetArticle;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public array|null $article,
    ) {
    }

    public function data(): array
    {
        // Return data directly without wrapping
        return $this->article ?? [];
    }
}
