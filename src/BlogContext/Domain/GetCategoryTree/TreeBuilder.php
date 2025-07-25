<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategoryTree;

use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final readonly class TreeBuilder implements TreeBuilderInterface
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
    ) {
    }

    #[\Override]
    public function __invoke(CategoryId|null $rootId, int $maxDepth): TreeData
    {
        // If a root ID is specified, start from that category
        if ($rootId instanceof CategoryId) {
            $rootCategory = $this->repository->findById($rootId);
            if (!$rootCategory instanceof \App\BlogContext\Domain\Shared\Model\Category) {
                return new TreeData([]);
            }
            $categories = $this->repository->findByParentId($rootId);
        } else {
            // Otherwise, get all root categories
            $categories = $this->repository->findByParentId(null);
        }

        $nodes = $this->buildNodes($categories, $maxDepth, 1);

        return new TreeData($nodes);
    }

    /**
     * @param array<\App\BlogContext\Domain\Shared\Model\Category> $categories
     *
     * @return array<array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     description: string|null,
     *     order: int,
     *     children: array
     * }>
     */
    private function buildNodes(array $categories, int $maxDepth, int $currentDepth): array
    {
        $nodes = [];

        foreach ($categories as $category) {
            $node = [
                'id' => $category->id->getValue(),
                'name' => $category->name->getValue(),
                'slug' => $category->slug->getValue(),
                'description' => $category->description instanceof \App\BlogContext\Domain\Shared\ValueObject\Description ? $category->description->getValue() : null,
                'order' => $category->order->getValue(),
                'children' => [],
            ];

            // Only fetch children if we haven't reached max depth
            if ($currentDepth < $maxDepth) {
                $children = $this->repository->findByParentId($category->id);
                $node['children'] = $this->buildNodes($children, $maxDepth, $currentDepth + 1);
            }

            $nodes[] = $node;
        }

        // Sort by order
        usort($nodes, fn ($a, $b) => $a['order'] <=> $b['order']);

        return $nodes;
    }
}
