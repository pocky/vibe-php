<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\SubmitForReview\Middleware;

use App\BlogContext\Application\Gateway\SubmitForReview\Request;
use App\Shared\Application\Gateway\GatewayException;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Validation
{
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if (!$request instanceof Request) {
            throw new GatewayException('Invalid request type', new \InvalidArgumentException('Invalid request type'));
        }

        // Article ID is already validated in Request::fromData()
        // Add any additional business validation here if needed

        /** @var GatewayResponse */
        return $next($request);
    }
}
