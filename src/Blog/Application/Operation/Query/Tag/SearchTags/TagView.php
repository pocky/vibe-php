<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Tag\SearchTags;

final readonly class TagView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
