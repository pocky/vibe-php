<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Translation;

/**
 * Domain-level translator interface to keep domain layer pure
 * Implementation will be provided by infrastructure layer
 */
interface TranslatorInterface
{
    /**
     * Translates a message with ICU MessageFormat support
     *
     * @param array<string, mixed> $parameters
     */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string;
}