<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteAuthor\Event;

final readonly class AuthorDeleted
{
    public function __construct(
        private string $authorId,
        private \DateTimeImmutable $deletedAt,
    ) {
    }

    public function authorId(): string
    {
        return $this->authorId;
    }

    public function deletedAt(): \DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function eventType(): string
    {
        return 'Blog.Author.Deleted';
    }

    public function aggregateId(): string
    {
        return $this->authorId;
    }

    public function toArray(): array
    {
        return [
            'authorId' => $this->authorId,
            'deletedAt' => $this->deletedAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
