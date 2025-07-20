<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Content implements \Stringable
{
    private const int MIN_LENGTH = 1;
    private const int EXCERPT_LENGTH = 200;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.content.empty');
        }

        if (self::MIN_LENGTH > mb_strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.content.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => mb_strlen($trimmed),
            ]);
        }

        $this->value = $trimmed;
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

    public function getExcerpt(int $length = self::EXCERPT_LENGTH): string
    {
        $plainText = strip_tags($this->value);
        if (mb_strlen($plainText) <= $length) {
            return $plainText;
        }

        return mb_substr($plainText, 0, $length) . '...';
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->value));
    }
}
