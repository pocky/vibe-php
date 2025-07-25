<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteAuthor\Model;

use App\BlogContext\Domain\DeleteAuthor\Event\AuthorDeleted;
use App\BlogContext\Domain\Shared\ValueObject\AuthorId;

/**
 * Represents author aggregate root during deletion.
 * This model emits domain events and encapsulates deletion logic.
 */
final class Author
{
    private array $events = [];

    public function __construct(
        private readonly AuthorId $id,
        private readonly \DateTimeImmutable $deletedAt,
    ) {
    }

    public static function delete(
        AuthorId $id,
        \DateTimeImmutable $deletedAt,
    ): self {
        $author = new self(
            id: $id,
            deletedAt: $deletedAt,
        );

        // Emit domain event
        $author->events[] = new AuthorDeleted(
            $id->getValue(),
            $deletedAt
        );

        return $author;
    }

    public function id(): AuthorId
    {
        return $this->id;
    }

    public function deletedAt(): \DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
