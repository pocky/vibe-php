<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Author\CreateAuthor;

use App\Blog\Domain\Author\Shared\Identifier\AuthorId;

final readonly class Command
{
    public function __construct(
        public AuthorId $authorId,
        public string $name,
        public string $email,
        public string $bio,
    ) {
        if ('' === trim($name)) {
            throw new \InvalidArgumentException('Author name cannot be empty');
        }

        if ('' === trim($email)) {
            throw new \InvalidArgumentException('Author email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }
}
