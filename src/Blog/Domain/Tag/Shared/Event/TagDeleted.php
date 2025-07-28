<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Event;

final readonly class TagDeleted
{
    public function __construct(
        public string $tagId,
        public \DateTimeImmutable $deletedAt,
    ) {
    }

    public function eventType(): string
    {
        return 'Blog.Tag.Deleted';
    }

    public function aggregateId(): string
    {
        return $this->tagId;
    }

    public function toArray(): array
    {
        return [
            'tagId' => $this->tagId,
            'deletedAt' => $this->deletedAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
