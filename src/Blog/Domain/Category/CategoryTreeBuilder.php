<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category;

use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;

final readonly class CategoryTreeBuilder
{
    public function __construct(
        private CategoryReadRepositoryInterface $categoryReadRepository,
    ) {
    }

    /**
     * Build hierarchical tree structure of categories.
     *
     * @return array<array{category: CategoryReadModel, children: array}>
     */
    public function __invoke(): array
    {
        $allCategories = $this->categoryReadRepository->findAll();

        if ([] === $allCategories) {
            return [];
        }

        // Index categories by ID for efficient lookup
        $categoriesById = [];
        foreach ($allCategories as $category) {
            $categoriesById[$category->id->getValue()] = $category;
        }

        // Separate root categories and group children by parent ID
        $rootCategories = [];
        $childrenByParentId = [];

        foreach ($allCategories as $category) {
            if ($category->isRoot()) {
                $rootCategories[] = $category;
            } else {
                $parentId = $category->parentId?->getValue();

                // Only add as child if parent exists in the current dataset
                if (isset($categoriesById[$parentId])) {
                    $childrenByParentId[$parentId][] = $category;
                } else {
                    // Treat orphaned categories as root categories
                    $rootCategories[] = $category;
                }
            }
        }

        // Sort root categories by order
        usort($rootCategories, fn (CategoryReadModel $a, CategoryReadModel $b): int => $a->order->getValue() <=> $b->order->getValue()
        );

        // Build tree recursively
        return array_map(
            fn (CategoryReadModel $category): array => $this->buildCategoryNode($category, $childrenByParentId),
            $rootCategories
        );
    }

    /**
     * Build a single category node with its children.
     *
     * @param array<string, CategoryReadModel[]> $childrenByParentId
     *
     * @return array{category: CategoryReadModel, children: array}
     */
    private function buildCategoryNode(CategoryReadModel $category, array $childrenByParentId): array
    {
        $categoryId = $category->id->getValue();
        $children = $childrenByParentId[$categoryId] ?? [];

        // Sort children by order
        usort($children, fn (CategoryReadModel $a, CategoryReadModel $b): int => $a->order->getValue() <=> $b->order->getValue()
        );

        // Recursively build children nodes
        $childrenNodes = array_map(
            fn (CategoryReadModel $child): array => $this->buildCategoryNode($child, $childrenByParentId),
            $children
        );

        return [
            'category' => $category,
            'children' => $childrenNodes,
        ];
    }
}
