<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Repository;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};

/**
 * Data transfer object for article retrieval from repository
 * Used for reconstituting domain aggregates from persistence
 */
final readonly class ArticleData
{
    public function __construct(
        public ArticleId $id,
        public Title $title,
        public Content $content,
        public Slug $slug,
        public ArticleStatus $status,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable|null $publishedAt = null,
        public \DateTimeImmutable|null $updatedAt = null,
    ) {
    }
}
