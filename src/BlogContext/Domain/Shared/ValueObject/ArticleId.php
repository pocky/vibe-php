<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use Symfony\Component\Uid\Uuid;

final class ArticleId
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }

        if (!Uuid::isValid($this->value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
