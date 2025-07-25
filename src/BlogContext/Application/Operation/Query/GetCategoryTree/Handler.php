<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategoryTree;

use App\BlogContext\Domain\GetCategoryTree\TreeBuilderInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private TreeBuilderInterface $treeBuilder,
    ) {
    }

    public function __invoke(Query $query): View
    {
        $rootId = $query->rootId ? new CategoryId($query->rootId) : null;
        $tree = ($this->treeBuilder)($rootId, $query->maxDepth);

        return new View(
            tree: $this->transformToNodes($tree->nodes),
        );
    }

    /**
     * @param array<array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     order: int,
     *     children: array
     * }> $nodes
     *
     * @return array<CategoryNode>
     */
    private function transformToNodes(array $nodes): array
    {
        return array_map(
            fn (array $node) => new CategoryNode(
                id: $node['id'],
                name: $node['name'],
                slug: $node['slug'],
                description: $node['description'],
                order: $node['order'],
                children: $this->transformToNodes($node['children']),
            ),
            $nodes,
        );
    }
}
