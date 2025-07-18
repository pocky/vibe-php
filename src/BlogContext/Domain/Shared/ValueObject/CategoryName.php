<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class CategoryName
{
    private const int MIN_LENGTH = 2;
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
            throw ValidationException::withTranslationKey('validation.category.name.empty');
        }

        if (self::MIN_LENGTH > strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.category.name.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($trimmed),
            ]);
        }

        if (self::MAX_LENGTH < strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.category.name.too_long', [
                'max_length' => self::MAX_LENGTH,
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

    // TODO: Add business methods as needed
    // public function toString(): string
    // {
    //     return $this->value;
    // }
}
