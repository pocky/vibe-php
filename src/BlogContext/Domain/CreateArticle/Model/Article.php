<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle\Model;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;

/**
 * Represents article data during creation.
 * This is a data transfer object specific to the CreateArticle operation.
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
        private array $events = []
    ) {
    }

    public static function create(
        ArticleId $id,
        Title $title,
        Content $content,
        Slug $slug,
        string $authorId,
    ): self {
        return new self(
            id: $id,
            title: $title,
            content: $content,
            slug: $slug,
            status: ArticleStatus::DRAFT,
            authorId: $authorId,
            timestamps: Timestamps::create(),
            events: [],
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
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
