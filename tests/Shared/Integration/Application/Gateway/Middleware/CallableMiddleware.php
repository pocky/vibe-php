<?php

declare(strict_types=1);

namespace App\Tests\Shared\Integration\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class CallableMiddleware
{
    public function __construct(
        private \Closure $callback,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        return ($this->callback)($request);
    }
}
