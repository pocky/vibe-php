<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\ApproveArticle\Middleware;

use App\BlogContext\Application\Gateway\ApproveArticle\Request;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Validation
{
    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        if (!$request instanceof Request) {
            throw new \RuntimeException('Invalid request type for ApproveArticle validation');
        }

        // Request validation is already handled in Request::fromData()
        // Additional business validation can be added here if needed

        /** @var GatewayResponse */
        return $next($request);
    }
}
