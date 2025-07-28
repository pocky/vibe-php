<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Event;

final readonly class AuthorDeleted
{
    public function __construct(
        public string $authorId,
        public \DateTimeImmutable $deletedAt,
    ) {
    }
}
