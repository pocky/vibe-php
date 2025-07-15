<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway;

use App\Shared\Application\Gateway\Middleware\Pipe;

abstract class DefaultGateway
{
    public function __construct(
        protected array $middlewares,
    ) {
    }

    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        return new Pipe($this->middlewares)($request);
    }
}
