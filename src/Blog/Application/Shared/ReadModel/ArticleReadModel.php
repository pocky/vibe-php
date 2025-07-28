<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\ReadModel;

use App\Blog\Domain\Article\Shared\Identifier\ArticleId;
use App\Blog\Domain\Article\Shared\ValueObject\ArticleStatus;
use App\Blog\Domain\Article\Shared\ValueObject\Content;
use App\Blog\Domain\Article\Shared\ValueObject\Title;
use App\Blog\Domain\Shared\ValueObject\Slug;
use App\Blog\Domain\Shared\ValueObject\Timestamps;

/**
 * Read model for article data.
 * Used for query operations and represents the current state of an article.
 */
final readonly class ArticleReadModel
{
    public function __construct(
        public ArticleId $id,
        public Title $title,
        public Content $content,
        public Slug $slug,
        public ArticleStatus $status,
        public string $authorId,
        public string $excerpt,
        public Timestamps $timestamps,
        public \DateTimeImmutable|null $publishedAt = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'title' => $this->title->getValue(),
            'content' => $this->content->getValue(),
            'slug' => $this->slug->getValue(),
            'status' => $this->status->value,
            'authorId' => $this->authorId,
            'excerpt' => $this->excerpt,
            'createdAt' => $this->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'publishedAt' => $this->publishedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
