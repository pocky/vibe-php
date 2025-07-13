<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Instrumentation;

use App\Shared\Application\Gateway\GatewayRequest;
use App\Shared\Application\Gateway\GatewayResponse;
use App\Shared\Infrastructure\Instrumentation\LoggerInstrumentation;
use Psr\Log\LoggerInterface;

class DefaultGatewayInstrumentation implements GatewayInstrumentation
{
    private readonly LoggerInterface $logger;

    public function __construct(
        LoggerInstrumentation $loggerInstrumentation,
        private readonly string $name,
    ) {
        $this->logger = $loggerInstrumentation->getLogger();
    }

    #[\Override]
    public function start(GatewayRequest $gatewayRequest): void
    {
        $this->logger->info($this->name, $gatewayRequest->data());
    }

    #[\Override]
    public function success(GatewayResponse $gatewayResponse): void
    {
        $this->logger->info(\sprintf('%s.success', $this->name), $gatewayResponse->data());
    }

    #[\Override]
    public function error(GatewayRequest $gatewayRequest, string $reason): void
    {
        $this->logger->error(\sprintf('%s.error', $this->name), [
            ...$gatewayRequest->data(), ...[
                ' reason' => $reason,
            ]]);
    }
}
