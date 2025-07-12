<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle\Event;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Title};

final readonly class ArticleUpdated
{
    /**
     * @param string[] $changedFields
     */
    public function __construct(
        private ArticleId $articleId,
        private Title $title,
        private \DateTimeImmutable $updatedAt,
        private array $changedFields,
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

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return string[]
     */
    public function changedFields(): array
    {
        return $this->changedFields;
    }

    public function eventType(): string
    {
        return 'BlogContext.Article.Updated';
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
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
            'changedFields' => $this->changedFields,
            'eventType' => $this->eventType(),
        ];
    }
}
