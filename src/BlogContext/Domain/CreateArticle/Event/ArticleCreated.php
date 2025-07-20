<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Event;

final readonly class ArticleCreated
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $authorId,
        public string $status,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
