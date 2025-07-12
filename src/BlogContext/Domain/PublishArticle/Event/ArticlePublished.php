<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\PublishArticle\Event;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Slug, Title};

final readonly class ArticlePublished
{
    public function __construct(
        private ArticleId $articleId,
        private Title $title,
        private Slug $slug,
        private \DateTimeImmutable $publishedAt,
    ) {
    }

    public function articleId(): ArticleId
    {
        return $this->articleId;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function slug(): Slug
    {
        return $this->slug;
    }

    public function publishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function eventType(): string
    {
        return 'BlogContext.Article.Published';
    }

    public function aggregateId(): string
    {
        return $this->articleId->getValue();
    }

    public function toArray(): array
    {
        return [
            'articleId' => $this->articleId->getValue(),
            'title' => $this->title->getValue(),
            'slug' => $this->slug->getValue(),
            'publishedAt' => $this->publishedAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
