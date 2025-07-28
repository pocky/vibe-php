<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Tag\DeleteTag;

final readonly class Command
{
    public function __construct(
        public string $tagId,
    ) {
    }
}
