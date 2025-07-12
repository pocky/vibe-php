<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\DataPersister;

use App\BlogContext\Domain\PublishArticle\Event\ArticlePublished;
use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};

final class Article
{
    private array $domainEvents = [];

    public function __construct(
        public ArticleId $id {
            get => $this->id;
        },
        public Title $title {
            get => $this->title;
        },
        public Content $content {
            get => $this->content;
        },
        public Slug $slug {
            get => $this->slug;
        },
        public ArticleStatus $status {
            get => $this->status;
        },
        public \DateTimeImmutable $createdAt {
            get => $this->createdAt;
        },
        public \DateTimeImmutable $publishedAt {
            get => $this->publishedAt;
        },
    ) {
        // Emit domain event on publication
        $this->domainEvents[] = new ArticlePublished(
            articleId: $this->id,
            title: $this->title,
            slug: $this->slug,
            publishedAt: $this->publishedAt
        );
    }

    // Domain event management
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    public function hasUnreleasedEvents(): bool
    {
        return [] !== $this->domainEvents;
    }
}
