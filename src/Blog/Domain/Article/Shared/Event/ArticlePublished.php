<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Event;

final readonly class ArticlePublished
{
    public function __construct(
        public string $articleId,
        public \DateTimeImmutable $publishedAt,
    ) {
    }
}
