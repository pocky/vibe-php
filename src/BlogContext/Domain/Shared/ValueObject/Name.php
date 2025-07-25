<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Name implements \Stringable
{
    private const int MIN_LENGTH = 1;
    private const int MAX_LENGTH = 100;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.name.empty');
        }

        if (self::MIN_LENGTH > mb_strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.name.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => mb_strlen($trimmed),
            ]);
        }

        if (self::MAX_LENGTH < mb_strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.name.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => mb_strlen($trimmed),
            ]);
        }

        $this->value = $trimmed;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
