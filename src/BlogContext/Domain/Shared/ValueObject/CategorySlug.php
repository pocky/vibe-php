<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class CategorySlug
{
    private const int MIN_LENGTH = 3;
    private const int MAX_LENGTH = 250;
    private const string PATTERN = '/^[a-z0-9-]+$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.category.slug.empty');
        }

        if (self::MIN_LENGTH > strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.category.slug.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.category.slug.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            throw ValidationException::withTranslationKey('validation.category.slug.invalid_format');
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
