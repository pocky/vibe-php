<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\SubmitForReview\Event;

use App\BlogContext\Domain\Shared\Event\DomainEvent;

final readonly class ArticleSubmittedForReview implements DomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        public string $articleId,
        public string $title,
        public string $authorId,
        public \DateTimeImmutable $submittedAt,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getName(): string
    {
        return 'blog.article.submitted_for_review';
    }

    #[\Override]
    public function getPayload(): array
    {
        return [
            'articleId' => $this->articleId,
            'title' => $this->title,
            'authorId' => $this->authorId,
            'submittedAt' => $this->submittedAt->format(\DateTimeInterface::ATOM),
        ];
    }

    #[\Override]
    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    #[\Override]
    public function getAggregateId(): string
    {
        return $this->articleId;
    }
}
