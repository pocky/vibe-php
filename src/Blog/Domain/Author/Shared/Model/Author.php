<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Model;

use App\Blog\Domain\Author\Shared\Event\AuthorCreated;
use App\Blog\Domain\Author\Shared\Event\AuthorUpdated;
use App\Blog\Domain\Author\Shared\Identifier\AuthorId;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorBio;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorEmail;
use App\Blog\Domain\Author\Shared\ValueObject\AuthorName;

/**
 * Author aggregate root with rich business logic.
 */
final class Author
{
    private array $events = [];

    public function __construct(
        private readonly AuthorId $authorId,
        private AuthorName $authorName,
        private AuthorEmail $authorEmail,
        private AuthorBio $authorBio,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        AuthorId $authorId,
        AuthorName $authorName,
        AuthorEmail $authorEmail,
        AuthorBio $authorBio,
    ): self {
        $now = new \DateTimeImmutable();

        $author = new self(
            authorId: $authorId,
            authorName: $authorName,
            authorEmail: $authorEmail,
            authorBio: $authorBio,
            createdAt: $now,
            updatedAt: $now,
        );

        $author->recordEvent(new AuthorCreated(
            authorId: $authorId->getValue(),
            name: $authorName->getValue(),
            email: $authorEmail->getValue(),
            bio: $authorBio->getValue(),
            createdAt: $now,
        ));

        return $author;
    }

    public function update(
        AuthorName|null $name = null,
        AuthorEmail|null $email = null,
        AuthorBio|null $bio = null,
    ): void {
        $hasChanges = false;

        if ($name instanceof AuthorName && !$this->authorName->equals($name)) {
            $this->authorName = $name;
            $hasChanges = true;
        }

        if ($email instanceof AuthorEmail && !$this->authorEmail->equals($email)) {
            $this->authorEmail = $email;
            $hasChanges = true;
        }

        if ($bio instanceof AuthorBio && !$this->authorBio->equals($bio)) {
            $this->authorBio = $bio;
            $hasChanges = true;
        }

        if ($hasChanges) {
            $this->updatedAt = new \DateTimeImmutable();

            $this->recordEvent(new AuthorUpdated(
                authorId: $this->authorId->getValue(),
                name: $this->authorName->getValue(),
                email: $this->authorEmail->getValue(),
                bio: $this->authorBio->getValue(),
                updatedAt: $this->updatedAt,
            ));
        }
    }

    private function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    // Getters
    public function getId(): AuthorId
    {
        return $this->authorId;
    }

    public function id(): AuthorId
    {
        return $this->authorId;
    }

    public function getName(): AuthorName
    {
        return $this->authorName;
    }

    public function name(): AuthorName
    {
        return $this->authorName;
    }

    public function getEmail(): AuthorEmail
    {
        return $this->authorEmail;
    }

    public function email(): AuthorEmail
    {
        return $this->authorEmail;
    }

    public function getBio(): AuthorBio
    {
        return $this->authorBio;
    }

    public function bio(): AuthorBio
    {
        return $this->authorBio;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
