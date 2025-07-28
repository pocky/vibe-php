<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Instrumentation;

use Psr\Log\LoggerInterface;

class LoggerInstrumentation implements Instrumentation
{
    public function __construct(
        public LoggerInterface $logger {
            get => $this->logger;
        },
    ) {
    }

    #[\Override]
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
