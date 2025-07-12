<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\{GatewayRequest, GatewayResponse, Instrumentation\GatewayInstrumentation};

final readonly class DefaultLogger
{
    public function __construct(
        private GatewayInstrumentation $instrumentation,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        $this->instrumentation->start($request);
        /** @var GatewayResponse $response */
        $response = ($next)($request);
        $this->instrumentation->success($response);

        return $response;
    }
}
