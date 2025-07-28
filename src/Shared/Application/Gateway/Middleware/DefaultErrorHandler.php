<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Middleware;

use App\Shared\Application\Gateway\GatewayException;
use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Application\Gateway\Instrumentation\GatewayInstrumentation;

final readonly class DefaultErrorHandler
{
    public function __construct(
        private GatewayInstrumentation $gatewayInstrumentation,
        private string $context,
        private string $entity,
        private string $operationType,
    ) {
    }

    public function __invoke(GatewayRequest $gatewayRequest, callable $next): GatewayResponse
    {
        try {
            /** @var GatewayResponse */
            return ($next)($gatewayRequest);
        } catch (\Exception $exception) {
            $this->gatewayInstrumentation->error($gatewayRequest, $exception->getMessage());

            throw new GatewayException(sprintf('Error during %s process for %s %s', $this->operationType, $this->context, $this->entity), $exception);
        }
    }
}
