<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Category\GetCategoryTree;

use App\Blog\Domain\Category\CategoryTreeBuilder;

final readonly class Handler
{
    public function __construct(
        private CategoryTreeBuilder $treeBuilder,
    ) {
    }

    public function __invoke(): View
    {
        // Use the domain service to build the hierarchical tree
        $tree = ($this->treeBuilder)();

        return new View($tree);
    }
}
