<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final class Title
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed || '0' === $trimmed) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (5 > strlen($trimmed)) {
            throw new \InvalidArgumentException('Title must be at least 5 characters');
        }

        if (200 < strlen($this->value)) {
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
