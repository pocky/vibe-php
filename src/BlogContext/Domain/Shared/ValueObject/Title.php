<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Title
{
    private const int MIN_LENGTH = 5;
    private const int MAX_LENGTH = 200;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.article.title.empty');
        }

        if (self::MIN_LENGTH > strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.article.title.too_short', [
                'min_length' => self::MIN_LENGTH,
            ]);
        }

        if (self::MAX_LENGTH < strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.article.title.too_long', [
                'max_length' => self::MAX_LENGTH,
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
