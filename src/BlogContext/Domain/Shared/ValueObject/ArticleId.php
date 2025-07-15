<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Translation\TranslatorInterface;
use Symfony\Component\Uid\Uuid;

final class ArticleId
{
    public function __construct(
        private(set) string $value,
        private ?TranslatorInterface $translator = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            $message = $this->translator?->trans('validation.article.id.invalid_uuid', [], 'messages')
                ?? 'Invalid UUID format';
            throw new \InvalidArgumentException($message);
        }

        if (!Uuid::isValid($this->value)) {
            $message = $this->translator?->trans('validation.article.id.invalid_uuid', [], 'messages')
                ?? 'Invalid UUID format';
            throw new \InvalidArgumentException($message);
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
