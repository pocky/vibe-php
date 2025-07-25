<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthorArticles;

final readonly class ArticleView
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public string $status,
        public \DateTimeImmutable|null $publishedAt,
    ) {
    }
}
