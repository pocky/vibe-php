<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Generator;

use Symfony\Component\Uid\Uuid;

final class UuidGenerator implements GeneratorInterface
{
    #[\Override]
    public static function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
