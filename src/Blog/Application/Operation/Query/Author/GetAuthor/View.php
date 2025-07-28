<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Author\GetAuthor;

final readonly class View
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
