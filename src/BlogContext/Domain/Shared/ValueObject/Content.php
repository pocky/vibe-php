<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Content
{
    private const int MIN_LENGTH = 10;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.article.content.empty');
        }

        if (self::MIN_LENGTH > strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.article.content.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($trimmed),
            ]);
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
