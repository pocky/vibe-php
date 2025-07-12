<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\{GatewayException,
    GatewayRequest,
    GatewayResponse,
    Instrumentation\GatewayInstrumentation
};

final readonly class DefaultErrorHandler
{
    public function __construct(
        private GatewayInstrumentation $instrumentation,
        private string $context,
        private string $entity,
        private string $operationType,
    ) {
    }

    public function __invoke(GatewayRequest $request, callable $next): GatewayResponse
    {
        try {
            /** @var GatewayResponse $response */
            $response = ($next)($request);

            return $response;
        } catch (\Throwable $exception) {
            $this->instrumentation->error($request, $exception->getMessage());

            throw new GatewayException(sprintf('Error during %s process for %s %s', $this->operationType, $this->context, $this->entity), $exception);
        }
    }
}
