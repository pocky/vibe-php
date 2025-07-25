<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthorArticles;

final readonly class View
{
    /**
     * @param ArticleView[] $articles
     */
    public function __construct(
        public string $authorId,
        public array $articles,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }
}
