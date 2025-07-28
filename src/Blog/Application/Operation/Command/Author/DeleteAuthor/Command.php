<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Author\DeleteAuthor;

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
