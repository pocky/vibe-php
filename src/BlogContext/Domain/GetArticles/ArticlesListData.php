<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticles;

/**
 * Represents a list of articles with pagination metadata.
 * This is a data transfer object specific to the GetArticles operation.
 */
final readonly class ArticlesListData
{
    /**
     * @param array<Model\Article> $articles
     */
    public function __construct(
        public array $articles,
        public int $total,
        public int $page,
        public int $limit,
        public int $totalPages,
    ) {
    }

    public static function create(
        array $articles,
        int $total,
        int $page,
        int $limit,
    ): self {
        $totalPages = (int) ceil($total / $limit);

        return new self(
            articles: $articles,
            total: $total,
            page: $page,
            limit: $limit,
            totalPages: $totalPages,
        );
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return 1 < $this->page;
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->articles);
    }
}
