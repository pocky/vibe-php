<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\ValueObject;

use App\Blog\Domain\Shared\Exception\ValidationException;

final class TagName implements \Stringable
{
    private const int MIN_LENGTH = 3;

    private const int MAX_LENGTH = 100;

    private const string PATTERN = '/^[a-zA-Z0-9\s\-]+$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->value = $this->normalize($value);
        $this->validate();
    }

    private function normalize(string $value): string
    {
        // Trim whitespace
        $value = trim($value);

        // Normalize multiple spaces to single space
        $normalized = preg_replace('/\s+/', ' ', $value);

        return $normalized ?? $value;
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.tag_name.empty');
        }

        // Length validation
        if (self::MIN_LENGTH > strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.tag_name.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.tag_name.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        // Pattern validation
        if (in_array(preg_match(self::PATTERN, $this->value), [0, false], true)) {
            throw ValidationException::withTranslationKey('validation.tag_name.invalid_format');
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
