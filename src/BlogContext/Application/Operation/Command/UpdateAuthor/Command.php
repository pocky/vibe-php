<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateAuthor;

final readonly class Command
{
    public function __construct(
        public string $authorId,
        public string $name,
        public string $email,
        public string $bio,
    ) {
        if ('' === trim($authorId)) {
            throw new \InvalidArgumentException('Author ID cannot be empty');
        }

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
