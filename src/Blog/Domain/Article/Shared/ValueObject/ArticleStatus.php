<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\ValueObject;

enum ArticleStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public function isDraft(): bool
    {
        return self::DRAFT === $this;
    }

    public function isPublished(): bool
    {
        return self::PUBLISHED === $this;
    }
}
