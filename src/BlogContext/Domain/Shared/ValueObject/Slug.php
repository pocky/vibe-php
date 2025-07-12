<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

final class Slug
{
    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    private const int MAX_LENGTH = 250;

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value || '0' === $this->value) {
            throw new \InvalidArgumentException('Slug cannot be empty');
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            throw new \InvalidArgumentException('Invalid slug format');
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            throw new \InvalidArgumentException('Slug cannot exceed 250 characters');
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
