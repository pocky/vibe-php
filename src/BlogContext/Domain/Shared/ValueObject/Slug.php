<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Slug implements \Stringable
{
    private const int MIN_LENGTH = 1;
    private const int MAX_LENGTH = 250;
    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.slug.empty');
        }

        if (self::MIN_LENGTH > strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.slug.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.slug.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            throw ValidationException::withTranslationKey('validation.slug.invalid_format');
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

    public static function fromTitle(Title $title): self
    {
        $slug = mb_strtolower($title->getValue());
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');

        if (self::MAX_LENGTH < strlen($slug)) {
            $slug = substr($slug, 0, self::MAX_LENGTH);
            $slug = rtrim($slug, '-');
        }

        return new self($slug);
    }
}
