<?php

declare(strict_types=1);

namespace App\Blog\Domain\Tag\Shared\Event;

final readonly class TagUpdated
{
    public function __construct(
        public string $tagId,
        public string $name,
        public string $slug,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function eventType(): string
    {
        return 'Blog.Tag.Updated';
    }

    public function aggregateId(): string
    {
        return $this->tagId;
    }

    public function toArray(): array
    {
        return [
            'tagId' => $this->tagId,
            'name' => $this->name,
            'slug' => $this->slug,
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
