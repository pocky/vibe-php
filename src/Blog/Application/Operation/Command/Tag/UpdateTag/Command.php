<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Tag\UpdateTag;

final readonly class Command
{
    public function __construct(
        public string $tagId,
        public string $name,
        public string $slug,
    ) {
    }
}
