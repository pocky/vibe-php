<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateAuthor\Event;

final readonly class AuthorUpdated
{
    public function __construct(
        private string $authorId,
        private string $name,
        private string $email,
        private string $bio,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public function authorId(): string
    {
        return $this->authorId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function bio(): string
    {
        return $this->bio;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function eventType(): string
    {
        return 'Blog.Author.Updated';
    }

    public function aggregateId(): string
    {
        return $this->authorId;
    }

    public function toArray(): array
    {
        return [
            'authorId' => $this->authorId,
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
