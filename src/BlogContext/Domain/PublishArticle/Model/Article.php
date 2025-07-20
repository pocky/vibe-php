<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Model;

use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;

/**
 * Represents article data during publish operations.
 * This is a data transfer object specific to the PublishArticle operation.
 */
final readonly class Article
{
    public function __construct(
        public ArticleId $id,
        public Slug $slug,
        public ArticleStatus $status,
        public Timestamps $timestamps,
        public \DateTimeImmutable|null $publishedAt = null,
        private array $events = []
    ) {
    }

    public static function fromReadModel(ArticleReadModel $readModel): self
    {
        return new self(
            id: $readModel->id,
            slug: $readModel->slug,
            status: $readModel->status,
            timestamps: $readModel->timestamps,
            publishedAt: $readModel->publishedAt,
        );
    }

    public function publish(\DateTimeImmutable|null $publishAt = null): self
    {
        if ($this->status->isPublished()) {
            throw new \DomainException('Article is already published');
        }

        return new self(
            id: $this->id,
            slug: $this->slug,
            status: ArticleStatus::PUBLISHED,
            timestamps: $this->timestamps->withUpdatedNow(),
            publishedAt: $publishAt ?? new \DateTimeImmutable(),
            events: $this->events,
        );
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            slug: $this->slug,
            status: $this->status,
            timestamps: $this->timestamps,
            publishedAt: $this->publishedAt,
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function isPublished(): bool
    {
        return $this->status->isPublished();
    }
}
