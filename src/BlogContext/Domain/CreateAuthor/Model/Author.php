<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateAuthor\Model;

use App\BlogContext\Domain\CreateAuthor\Event\AuthorCreated;
use App\BlogContext\Domain\Shared\ValueObject\AuthorBio;
use App\BlogContext\Domain\Shared\ValueObject\AuthorEmail;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;
use App\BlogContext\Domain\Shared\ValueObject\AuthorName;

/**
 * Represents author aggregate root during creation.
 * This model emits domain events and encapsulates creation logic.
 */
final readonly class Author
{
    public function __construct(
        private AuthorId $id,
        private AuthorName $name,
        private AuthorEmail $email,
        private AuthorBio $bio,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
        private array $events = [],
    ) {
    }

    public static function create(
        AuthorId $id,
        AuthorName $name,
        AuthorEmail $email,
        AuthorBio $bio,
        \DateTimeImmutable $createdAt,
    ): self {
        // Create domain event
        $event = new AuthorCreated(
            $id->getValue(),
            $name->getValue(),
            $email->getValue(),
            $bio->getValue(),
            $createdAt
        );

        return new self(
            id: $id,
            name: $name,
            email: $email,
            bio: $bio,
            createdAt: $createdAt,
            updatedAt: $createdAt,
            events: [$event],
        );
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
        return $this->events;
    }

    public function withEvents(array $events): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            bio: $this->bio,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            events: $events,
        );
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
