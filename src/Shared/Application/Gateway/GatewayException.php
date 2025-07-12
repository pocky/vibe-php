<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway;

final class GatewayException extends \Exception
{
    public function __construct(
        private readonly string $context,
        \Throwable $previous,
    ) {
        parent::__construct(
            message: sprintf(
                '%s in %s: %s',
                $this->context,
                $previous->getFile(),
                $previous->getMessage(),
            ),
            previous: $previous,
        );
    }

    public function getContext(): string
    {
        return $this->context;
    }
}
