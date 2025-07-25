<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateAuthor\Event;

final readonly class AuthorCreated
{
    public function __construct(
        private string $authorId,
        private string $name,
        private string $email,
        private string $bio,
        private \DateTimeImmutable $createdAt,
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

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function eventType(): string
    {
        return 'Blog.Author.Created';
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
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
