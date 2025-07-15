<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Translation\TranslatorInterface;

final class Title
{
    private const int MIN_LENGTH = 5;
    private const int MAX_LENGTH = 200;

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
            $message = $this->translator?->trans('validation.article.title.empty', [], 'messages') 
                ?? 'Title cannot be empty';
            throw new \InvalidArgumentException($message);
        }

        if (self::MIN_LENGTH > strlen($trimmed)) {
            $message = $this->translator?->trans('validation.article.title.too_short', [
                'min_length' => self::MIN_LENGTH
            ], 'messages') ?? 'Title must be at least 5 characters';
            throw new \InvalidArgumentException($message);
        }

        if (self::MAX_LENGTH < strlen($trimmed)) {
            $message = $this->translator?->trans('validation.article.title.too_long', [
                'max_length' => self::MAX_LENGTH
            ], 'messages') ?? 'Title cannot exceed 200 characters';
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
