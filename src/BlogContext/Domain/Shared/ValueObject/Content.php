<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final class Content
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
            throw new \InvalidArgumentException('Content cannot be empty');
        }

        if (10 > strlen($trimmed)) {
            throw new \InvalidArgumentException('Content must be at least 10 characters');
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
