<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticles;

use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;

/**
 * Criteria for listing articles.
 */
final readonly class ListCriteria
{
    public function __construct(
        public ArticleStatus|null $status = null,
        public string|null $authorId = null,
        public int $page = 1,
        public int $limit = 20,
        public string $sortBy = 'createdAt',
        public string $sortOrder = 'DESC',
    ) {
        if (1 > $this->page) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if (1 > $this->limit || 100 < $this->limit) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }

        if (!in_array($this->sortBy, ['createdAt', 'updatedAt', 'publishedAt', 'title'], true)) {
            throw new \InvalidArgumentException('Invalid sort field');
        }

        if (!in_array($this->sortOrder, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException('Sort order must be ASC or DESC');
        }
    }

    public static function create(): self
    {
        return new self();
    }

    public function withStatus(ArticleStatus $status): self
    {
        return new self(
            status: $status,
            authorId: $this->authorId,
            page: $this->page,
            limit: $this->limit,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function withAuthor(string $authorId): self
    {
        return new self(
            status: $this->status,
            authorId: $authorId,
            page: $this->page,
            limit: $this->limit,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function withPagination(int $page, int $limit): self
    {
        return new self(
            status: $this->status,
            authorId: $this->authorId,
            page: $page,
            limit: $limit,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder,
        );
    }

    public function withSort(string $sortBy, string $sortOrder = 'DESC'): self
    {
        return new self(
            status: $this->status,
            authorId: $this->authorId,
            page: $this->page,
            limit: $this->limit,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        );
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
