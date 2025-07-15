<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Model;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;

/**
 * Generic article model for read operations
 * Used when reconstituting articles from the repository
 */
final readonly class Article
{
    public function __construct(
        private ArticleId $id,
        private Title $title,
        private Content $content,
        private Slug $slug,
        private ArticleStatus $status,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private \DateTimeImmutable|null $publishedAt = null,
    ) {
    }

    public function getId(): ArticleId
    {
        return $this->id;
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getStatus(): ArticleStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPublishedAt(): \DateTimeImmutable|null
    {
        return $this->publishedAt;
    }
}
