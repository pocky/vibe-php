<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\ValueObject;

use App\Blog\Domain\Shared\Exception\ValidationException;

final class CategorySlug implements \Stringable
{
    private const int MIN_LENGTH = 1;

    private const int MAX_LENGTH = 120;

    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.category_slug.empty');
        }

        if (self::MIN_LENGTH > strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.category_slug.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.category_slug.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (in_array(preg_match(self::PATTERN, $this->value), [0, false], true)) {
            throw ValidationException::withTranslationKey('validation.category_slug.invalid_format');
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

    public static function fromName(CategoryName $categoryName): self
    {
        $slug = mb_strtolower($categoryName->getValue());
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim((string) $slug, '-');

        if (self::MAX_LENGTH < strlen($slug)) {
            $slug = substr($slug, 0, self::MAX_LENGTH);
            $slug = rtrim($slug, '-');
        }

        return new self($slug);
    }
}
