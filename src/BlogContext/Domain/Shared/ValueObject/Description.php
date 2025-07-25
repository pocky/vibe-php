<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class Description implements \Stringable
{
    private const int MAX_LENGTH = 1000;

    public function __construct(
        private(set) ?string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (null === $this->value) {
            return;
        }

        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            $this->value = null;

            return;
        }

        if (self::MAX_LENGTH < mb_strlen($trimmed)) {
            throw ValidationException::withTranslationKey('validation.description.too_long', [
                'max_length' => self::MAX_LENGTH,
                'actual_length' => mb_strlen($trimmed),
            ]);
        }

        $this->value = $trimmed;
    }

    public function getValue(): string|null
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isEmpty(): bool
    {
        return null === $this->value;
    }

    public function toString(): string
    {
        return $this->value ?? '';
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }
}
