<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Tag\CreateTag;

final readonly class Command
{
    public function __construct(
        public string $name,
        public string|null $slug = null,
    ) {
        if ('' === $this->name) {
            throw new \InvalidArgumentException('Tag name cannot be empty');
        }
    }
}
