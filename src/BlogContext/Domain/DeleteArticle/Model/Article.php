<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteArticle\Model;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Slug;

/**
 * Represents article data during deletion.
 * This is a data transfer object specific to the DeleteArticle operation.
 */
final readonly class Article
{
    public function __construct(
        public ArticleId $id,
        public Slug $slug,
        public string $deletedBy,
        public \DateTimeImmutable $deletedAt,
        private array $events = []
    ) {
    }

    public static function create(
        ArticleId $id,
        Slug $slug,
        string $deletedBy,
    ): self {
        return new self(
            id: $id,
            slug: $slug,
            deletedBy: $deletedBy,
            deletedAt: new \DateTimeImmutable(),
            events: [],
        );
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            slug: $this->slug,
            deletedBy: $this->deletedBy,
            deletedAt: $this->deletedAt,
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
