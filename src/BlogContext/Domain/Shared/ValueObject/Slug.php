<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Slug
{
    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    private const int MAX_LENGTH = 250;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value || '0' === $this->value) {
            throw ValidationException::withTranslationKey('validation.article.slug.empty');
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            throw ValidationException::withTranslationKey('validation.article.slug.invalid_format');
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.article.slug.too_long', [
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
