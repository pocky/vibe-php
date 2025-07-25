<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthor;

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
