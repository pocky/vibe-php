<?php

declare(strict_types=1);

namespace App\Blog\Domain\Author\Shared\Event;

final readonly class AuthorUpdated
{
    public function __construct(
        public string $authorId,
        public string $name,
        public string $email,
        public string $bio,
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
