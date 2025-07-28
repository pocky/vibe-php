<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Category\DeleteCategory;

final readonly class Command
{
    public function __construct(
        public string $categoryId,
    ) {
        if ('' === trim($this->categoryId)) {
            throw new \InvalidArgumentException('Category ID cannot be empty');
        }
    }
}
