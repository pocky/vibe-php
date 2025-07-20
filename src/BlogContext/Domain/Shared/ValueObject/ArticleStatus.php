<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ValueObject;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public static function fromString(string $status): self
    {
        return self::from($status);
    }

    public function isDraft(): bool
    {
        return self::DRAFT === $this;
    }

    public function isPublished(): bool
    {
        return self::PUBLISHED === $this;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
