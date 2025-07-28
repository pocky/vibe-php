<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\{GatewayRequest, GatewayResponse, Instrumentation\GatewayInstrumentation};

final readonly class DefaultLogger
{
    public function __construct(
        private GatewayInstrumentation $gatewayInstrumentation,
    ) {
    }

    public function __invoke(GatewayRequest $gatewayRequest, callable $next): GatewayResponse
    {
        $this->gatewayInstrumentation->start($gatewayRequest);
        /** @var GatewayResponse $response */
        $response = ($next)($gatewayRequest);
        $this->gatewayInstrumentation->success($response);

        return $response;
    }
}
