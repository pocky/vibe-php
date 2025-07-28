<?php

declare(strict_types=1);

namespace App\Blog\Domain\Article\Shared\Event;

final readonly class ArticleCreated
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $content,
        public string $slug,
        public string $authorId,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
