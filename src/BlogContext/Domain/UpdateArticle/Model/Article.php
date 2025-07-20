<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle\Model;

use App\BlogContext\Domain\Shared\ReadModel\ArticleReadModel;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;

/**
 * Represents article data during update operations.
 * This is a data transfer object specific to the UpdateArticle operation.
 */
final readonly class Article
{
    public function __construct(
        public ArticleId $id,
        public Title $title,
        public Content $content,
        public Slug $slug,
        public ArticleStatus $status,
        public string $authorId,
        public Timestamps $timestamps,
        public \DateTimeImmutable|null $publishedAt = null,
        private array $events = [],
        private array $changes = []
    ) {
    }

    public static function fromReadModel(ArticleReadModel $readModel): self
    {
        return new self(
            id: $readModel->id,
            title: $readModel->title,
            content: $readModel->content,
            slug: $readModel->slug,
            status: $readModel->status,
            authorId: $readModel->authorId,
            timestamps: $readModel->timestamps,
            publishedAt: $readModel->publishedAt,
        );
    }

    public function update(
        Title $newTitle,
        Content $newContent,
        Slug $newSlug,
    ): self {
        $changes = [];

        if (!$this->title->equals($newTitle)) {
            $changes['title'] = [
                'old' => $this->title->getValue(),
                'new' => $newTitle->getValue(),
            ];
        }

        if (!$this->content->equals($newContent)) {
            $changes['content'] = [
                'old' => $this->content->getValue(),
                'new' => $newContent->getValue(),
            ];
        }

        if (!$this->slug->equals($newSlug)) {
            $changes['slug'] = [
                'old' => $this->slug->getValue(),
                'new' => $newSlug->getValue(),
            ];
        }

        if ([] === $changes) {
            return $this;
        }

        return new self(
            id: $this->id,
            title: $newTitle,
            content: $newContent,
            slug: $newSlug,
            status: $this->status,
            authorId: $this->authorId,
            timestamps: $this->timestamps->withUpdatedNow(),
            publishedAt: $this->publishedAt,
            events: $this->events,
            changes: $changes,
        );
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            content: $this->content,
            slug: $this->slug,
            status: $this->status,
            authorId: $this->authorId,
            timestamps: $this->timestamps,
            publishedAt: $this->publishedAt,
            events: $events,
            changes: $this->changes,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function hasChanges(): bool
    {
        return [] !== $this->changes;
    }
}
