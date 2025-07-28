<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Identifier;

use App\Blog\Domain\Shared\Exception\ValidationException;

final class CategoryId implements \Stringable
{
    public function __construct(
        private(set) string $value,
    ) {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.category_id.empty');
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

    public function __toString(): string
    {
        return $this->value;
    }
}
