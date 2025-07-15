<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final class Content
{
    private const int MIN_LENGTH = 10;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            throw new \InvalidArgumentException('Content cannot be empty');
        }

        if (self::MIN_LENGTH > strlen($trimmed)) {
            throw new \InvalidArgumentException('Content must be at least 10 characters long');
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
