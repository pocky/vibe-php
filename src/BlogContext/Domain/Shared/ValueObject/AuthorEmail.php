<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class AuthorEmail implements \Stringable
{
    private const int MAX_LENGTH = 255;

    public function __construct(
        private(set) string $value,
    ) {
        $this->value = mb_strtolower(trim($this->value));
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.author_email.empty');
        }

        if (self::MAX_LENGTH < mb_strlen($this->value)) {
            throw ValidationException::withTranslationKey('validation.author_email.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => mb_strlen($this->value),
            ]);
        }

        if (false === filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withTranslationKey('validation.author_email.invalid_format', [
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

    public function getDomain(): string
    {
        return explode('@', $this->value)[1] ?? '';
    }

    public function getLocalPart(): string
    {
        return explode('@', $this->value)[0] ?? '';
    }
}
