<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\DeleteAuthor;

final readonly class Command
{
    public function __construct(
        public string $authorId,
    ) {
        if ('' === trim($authorId)) {
            throw new \InvalidArgumentException('Author ID cannot be empty');
        }
    }
}
