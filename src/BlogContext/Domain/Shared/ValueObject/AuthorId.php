<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
use Symfony\Component\Uid\Uuid;

final class AuthorId implements \Stringable
{
    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.author_id.empty');
        }

        if (!Uuid::isValid($this->value)) {
            throw ValidationException::withTranslationKey('validation.author_id.invalid_uuid', [
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
}
