<?php

declare(strict_types=1);

namespace App\Shared\Application\Gateway\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsGateway
{
    /**
     * @param array<class-string> $middlewares
     */
    public function __construct(
        public string $context,
        public string $domain,
        public string $operation,
        public array $middlewares,
    ) {
    }
}
