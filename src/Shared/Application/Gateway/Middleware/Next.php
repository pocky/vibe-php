<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Next
{
    public function __construct(
        private \Closure $next,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        /** @var GatewayResponse */
        return ($this->next)($request);
    }
}
