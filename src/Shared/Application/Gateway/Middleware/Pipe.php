<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use Webmozart\Assert\Assert;

final readonly class Pipe
{
    /**
     * @param array<callable> $middlewares
     */
    public function __construct(
        private array $middlewares = [],
    ) {
    }

    public function __invoke(GatewayRequest $request, callable|null $next = null): GatewayResponse
    {
        $current = $next;
        for ($i = count($this->middlewares) - 1; 0 <= $i; --$i) {
            $middleware = $this->middlewares[$i];
            $current = static fn ($request) => $middleware($request, $current);
        }

        Assert::notNull($current);

        /** @var GatewayResponse $response */
        $response = $current($request);

        return $response;
    }
}
