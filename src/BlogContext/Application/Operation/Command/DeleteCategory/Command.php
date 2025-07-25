<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\DeleteCategory;

final readonly class Command
{
    public function __construct(
        public string $categoryId,
    ) {
    }
}
