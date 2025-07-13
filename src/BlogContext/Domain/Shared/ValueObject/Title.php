<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final class Title
{
    private const int MIN_LENGTH = 5;
    private const int MAX_LENGTH = 200;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (self::MIN_LENGTH > strlen($trimmed)) {
            throw new \InvalidArgumentException('Title must be at least 5 characters');
        }

        if (self::MAX_LENGTH < strlen($trimmed)) {
            throw new \InvalidArgumentException('Title cannot exceed 200 characters');
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
