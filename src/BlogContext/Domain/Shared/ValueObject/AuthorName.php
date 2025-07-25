<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class AuthorName implements \Stringable
{
    private const int MIN_LENGTH = 2;
    private const int MAX_LENGTH = 100;
    private const string PATTERN = '/^[a-zA-Z0-9\s\-\'\.]+$/u';

    public function __construct(
        private(set) string $value,
    ) {
        $this->value = trim($this->value);
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.author_name.empty');
        }

        $length = mb_strlen($this->value);

        if (self::MIN_LENGTH > $length) {
            throw ValidationException::withTranslationKey('validation.author_name.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => $length,
            ]);
        }

        if (self::MAX_LENGTH < $length) {
            throw ValidationException::withTranslationKey('validation.author_name.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => $length,
            ]);
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            throw ValidationException::withTranslationKey('validation.author_name.invalid_format', [
                'value' => $this->value,
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

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
