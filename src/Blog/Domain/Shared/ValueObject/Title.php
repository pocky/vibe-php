<?php

declare(strict_types=1);

namespace App\Blog\Domain\Shared\ValueObject;

use App\Blog\Domain\Shared\Exception\ValidationException;

final class Title implements \Stringable
{
    private const int MIN_LENGTH = 3;

    private const int MAX_LENGTH = 200;

    public function __construct(
        private(set) string $value,
    ) {
        $this->value = trim($value);
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.title.empty');
        }

        if (self::MIN_LENGTH > strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.title.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.title.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($this->value),
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

    public function __toString(): string
    {
        return $this->value;
    }
}
