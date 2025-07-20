<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle\Event;

final readonly class ArticleUpdated
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $slug,
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
