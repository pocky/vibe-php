<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Event;

final readonly class ArticlePublished
{
    public function __construct(
        public string $articleId,
        public string $slug,
        public \DateTimeImmutable $publishedAt,
    ) {
    }
}
