<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle\DataPersister;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};
use App\BlogContext\Domain\UpdateArticle\Event\ArticleUpdated;

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
        public \DateTimeImmutable $updatedAt {
            get => $this->updatedAt;
        },
        private readonly Title $originalTitle,
        private readonly Content $originalContent,
    ) {
        // Emit domain event on update
        $this->domainEvents[] = new ArticleUpdated(
            articleId: $this->id,
            title: $this->title,
            updatedAt: $this->updatedAt,
            changedFields: $this->getChangedFields()
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

    private function getChangedFields(): array
    {
        $changes = [];

        if (!$this->title->equals($this->originalTitle)) {
            $changes[] = 'title';
        }

        if (!$this->content->equals($this->originalContent)) {
            $changes[] = 'content';
        }

        return $changes;
    }
}
