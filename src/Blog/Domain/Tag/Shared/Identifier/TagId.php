<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Identifier;

use App\Blog\Domain\Shared\Exception\ValidationException;

final class TagId implements \Stringable
{
    public function __construct(
        private(set) string $value,
    ) {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.tag_id.empty');
        }

        // ULID validation: must be 26 characters, uppercase, using Crockford Base32 alphabet
        if (in_array(preg_match('/^[0123456789ABCDEFGHJKMNPQRSTVWXYZ]{26}$/', $this->value), [0, false], true)) {
            throw ValidationException::withTranslationKey('validation.tag_id.invalid_format');
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

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
