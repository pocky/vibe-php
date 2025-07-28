<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\ValueObject;

use App\Blog\Domain\Shared\Exception\ValidationException;

final class Title implements \Stringable
{
    private const int MIN_LENGTH = 1;

    private const int MAX_LENGTH = 200;

    public function __construct(
        private string $value {
            set
    {
        $trimmed = trim($value);

        if ('' === $trimmed) {
            throw ValidationException::withTranslationKey('validation.title.empty');
        }

        if (self::MIN_LENGTH > mb_strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.title.too_short', [
                'min_length' => self::MIN_LENGTH,
                'actual_length' => mb_strlen($trimmed),
            ]);
        }

        if (self::MAX_LENGTH < mb_strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.title.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => mb_strlen($trimmed),
            ]);
        }

        $this->value = $trimmed;
    }
        },
    )
    {
    }

    public static function fromString(string $value): self
    {
        return new self($value);
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
