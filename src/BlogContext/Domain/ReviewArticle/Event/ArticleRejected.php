<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\ReviewArticle\Event;

use App\BlogContext\Domain\Shared\Event\DomainEvent;

final readonly class ArticleRejected implements DomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        public string $articleId,
        public string $title,
        public string $reviewerId,
        public string $rejectionReason,
        public \DateTimeImmutable $reviewedAt,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getName(): string
    {
        return 'blog.article.rejected';
    }

    #[\Override]
    public function getPayload(): array
    {
        return [
            'articleId' => $this->articleId,
            'title' => $this->title,
            'reviewerId' => $this->reviewerId,
            'rejectionReason' => $this->rejectionReason,
            'reviewedAt' => $this->reviewedAt->format(\DateTimeInterface::ATOM),
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
