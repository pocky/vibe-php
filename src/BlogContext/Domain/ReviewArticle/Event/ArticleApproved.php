<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\ReviewArticle\Event;

use App\BlogContext\Domain\Shared\Event\DomainEvent;

final readonly class ArticleApproved implements DomainEvent
{
    private \DateTimeImmutable $occurredAt;

    public function __construct(
        public string $articleId,
        public string $title,
        public string $reviewerId,
        public string|null $approvalReason,
        public \DateTimeImmutable $reviewedAt,
    ) {
        $this->occurredAt = new \DateTimeImmutable();
    }

    #[\Override]
    public function getName(): string
    {
        return 'blog.article.approved';
    }

    #[\Override]
    public function getPayload(): array
    {
        return [
            'articleId' => $this->articleId,
            'title' => $this->title,
            'reviewerId' => $this->reviewerId,
            'approvalReason' => $this->approvalReason,
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
