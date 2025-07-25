<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListAuthors;

final readonly class AuthorView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $bio,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }
}
