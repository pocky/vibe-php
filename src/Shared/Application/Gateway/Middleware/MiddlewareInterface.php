<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

interface MiddlewareInterface
{
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse;
}
