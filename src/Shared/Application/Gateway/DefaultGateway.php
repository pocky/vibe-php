<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway;

use App\Shared\Application\Gateway\Middleware\Pipe;

/**
 * @template T of GatewayResponse
 */
class DefaultGateway
{
    public function __construct(
        protected(set) array $middlewares,
    ) {
    }

    /**
     * @return T
     */
    public function __invoke(GatewayRequest $request): GatewayResponse
    {
        return (new Pipe($this->middlewares))($request);
    }
}
