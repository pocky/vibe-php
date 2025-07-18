<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;

final class CategoryPath
{
    private const int MAX_DEPTH = 2;
    private const string SEPARATOR = '/';
    private const string PATTERN = '/^[a-z0-9-]+(\/[a-z0-9-]+)*$/';

    public function __construct(
        private(set) string $value,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ('' === $this->value) {
            throw ValidationException::withTranslationKey('validation.category.path.empty');
        }

        if (!preg_match(self::PATTERN, $this->value)) {
            throw ValidationException::withTranslationKey('validation.category.path.invalid_format');
        }

        $depth = substr_count($this->value, self::SEPARATOR) + 1;
        if (self::MAX_DEPTH < $depth) {
            throw ValidationException::withTranslationKey('validation.category.path.too_deep', [
                'max_depth' => self::MAX_DEPTH,
                'actual_depth' => $depth,
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

    public function getDepth(): int
    {
        return substr_count($this->value, self::SEPARATOR) + 1;
    }

    public function getParentPath(): self|null
    {
        $lastSlashPos = strrpos($this->value, self::SEPARATOR);
        if (false === $lastSlashPos) {
            return null; // Root category has no parent
        }

        return new self(substr($this->value, 0, $lastSlashPos));
    }

    public function appendChild(string $childSlug): self
    {
        return new self($this->value . self::SEPARATOR . $childSlug);
    }

    public function isRoot(): bool
    {
        return !str_contains($this->value, self::SEPARATOR);
    }
}
