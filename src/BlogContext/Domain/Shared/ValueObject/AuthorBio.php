<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class AuthorBio implements \Stringable
{
    private const int MAX_LENGTH = 1000;

    public function __construct(
        private(set) string $value,
    ) {
        $this->value = trim($this->value);
        $this->validate();
    }

    private function validate(): void
    {
        // Bio can be empty - it's optional
        if ('' !== $this->value && self::MAX_LENGTH < mb_strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.author_bio.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => mb_strlen($this->value),
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

    public function isEmpty(): bool
    {
        return '' === $this->value;
    }

    public function getExcerpt(int $length = 100): string
    {
        if (mb_strlen($this->value) <= $length) {
            return $this->value;
        }

        return mb_substr($this->value, 0, $length) . '...';
    }
}
