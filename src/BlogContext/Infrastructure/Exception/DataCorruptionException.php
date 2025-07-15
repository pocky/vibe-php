<?php

declare(strict_types=1);

namespace App\BlogContext\Infrastructure\Exception;

final class DataCorruptionException extends \RuntimeException
{
    public static function corruptedValueObject(string $valueObjectClass, string $corruptedValue, \Throwable $originalException): self
    {
        return new self(
            sprintf(
                'Data corruption detected in %s with value "%s": %s',
                $valueObjectClass,
                $corruptedValue,
                $originalException->getMessage()
            ),
            previous: $originalException
        );
    }

    public static function corruptedEntity(string $entityClass, string $entityId, \Throwable $originalException): self
    {
        return new self(
            sprintf(
                'Data corruption detected in entity %s (ID: %s): %s',
                $entityClass,
                $entityId,
                $originalException->getMessage()
            ),
            previous: $originalException
        );
    }
}
