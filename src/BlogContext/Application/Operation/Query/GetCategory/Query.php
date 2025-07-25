<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategory;

final readonly class Query
{
    public function __construct(
        public string $id,
    ) {
    }
}
