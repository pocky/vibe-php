<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateAuthor\Model;

use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;
use App\BlogContext\Domain\UpdateAuthor\Event\AuthorUpdated;

/**
 * Represents author aggregate root during update.
 * This model emits domain events and encapsulates update logic.
 */
final class Author
{
    private array $events = [];

    public function __construct(
        private readonly AuthorId $id,
        private readonly AuthorName $name,
        private readonly AuthorEmail $email,
        private readonly AuthorBio $bio,
        private readonly \DateTimeImmutable $createdAt,
        private readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function update(
        AuthorId $id,
        AuthorName $name,
        AuthorEmail $email,
        AuthorBio $bio,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        $author = new self(
            id: $id,
            name: $name,
            email: $email,
            bio: $bio,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        // Emit domain event
        $author->events[] = new AuthorUpdated(
            $id->getValue(),
            $name->getValue(),
            $email->getValue(),
            $bio->getValue(),
            $updatedAt
        );

        return $author;
    }

    public function id(): AuthorId
    {
        return $this->id;
    }

    public function name(): AuthorName
    {
        return $this->name;
    }

    public function email(): AuthorEmail
    {
        return $this->email;
    }

    public function bio(): AuthorBio
    {
        return $this->bio;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
