<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateAuthor;

final readonly class Command
{
    public function __construct(
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
