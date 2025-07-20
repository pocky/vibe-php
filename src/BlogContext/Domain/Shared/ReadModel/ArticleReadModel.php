<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\ReadModel;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Timestamps;
use App\BlogContext\Domain\Shared\ValueObject\Title;

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
            'createdAt' => $this->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            'publishedAt' => $this->publishedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
