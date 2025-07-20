<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteArticle\Event;

final readonly class ArticleDeleted
{
    public function __construct(
        public string $articleId,
        public string $slug,
        public string $deletedBy,
        public \DateTimeImmutable $deletedAt,
    ) {
    }
}
