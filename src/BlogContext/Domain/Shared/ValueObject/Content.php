<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Translation\TranslatorInterface;

final class Content
{
    private const int MIN_LENGTH = 10;

    public function __construct(
        private(set) string $value,
        private ?TranslatorInterface $translator = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $trimmed = trim($this->value);

        if ('' === $trimmed) {
            $message = $this->translator?->trans('validation.article.content.empty', [], 'messages')
                ?? 'Content cannot be empty';
            throw new \InvalidArgumentException($message);
        }

        if (self::MIN_LENGTH > strlen($trimmed)) {
            $message = $this->translator?->trans('validation.article.content.too_short', [
                'min_length' => self::MIN_LENGTH
            ], 'messages') ?? 'Content must be at least 10 characters long';
            throw new \InvalidArgumentException($message);
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
