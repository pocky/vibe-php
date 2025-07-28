<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\ValueObject;

use App\Blog\Domain\Shared\Exception\ValidationException;

final class TagSlug implements \Stringable
{
    private const int MIN_LENGTH = 3;

    private const int MAX_LENGTH = 100;

    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    public static function generateFromName(string $name): self
    {
        // Convert to lowercase
        $slug = mb_strtolower($name);

        // Transliterate special characters
        $slug = self::transliterate($slug);

        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');

        // Remove consecutive hyphens
        $slug = preg_replace('/-+/', '-', $slug) ?? $slug;

        return new self($slug);
    }

    private static function transliterate(string $text): string
    {
        $chars = [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
        ];

        return strtr($text, $chars);
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.tag_slug.empty');
        }

        // Length validation
        if (self::MIN_LENGTH > strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.tag_slug.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.tag_slug.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => strlen($this->value),
            ]);
        }

        // Pattern validation (lowercase letters, numbers, hyphens)
        if (in_array(preg_match(self::PATTERN, $this->value), [0, false], true)) {
            throw ValidationException::withTranslationKey('validation.tag_slug.invalid_format');
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
