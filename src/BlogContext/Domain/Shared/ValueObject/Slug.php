<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Translation\TranslatorInterface;

final class Slug
{
    private const string PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    private const int MAX_LENGTH = 250;

    public function __construct(
        private(set) string $value,
        private ?TranslatorInterface $translator = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value || '0' === $this->value) {
            $message = $this->translator?->trans('validation.article.slug.empty', [], 'messages')
                ?? 'Slug cannot be empty';
            throw new \InvalidArgumentException($message);
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            $message = $this->translator?->trans('validation.article.slug.invalid_format', [], 'messages')
                ?? 'Invalid slug format';
            throw new \InvalidArgumentException($message);
        }

        if (self::MAX_LENGTH < strlen($this->value)) {
            $message = $this->translator?->trans('validation.article.slug.too_long', [
                'max_length' => self::MAX_LENGTH
            ], 'messages') ?? 'Slug cannot exceed 250 characters';
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
