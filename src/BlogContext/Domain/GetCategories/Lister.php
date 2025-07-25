<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategories;

use App\BlogContext\Domain\GetCategories\Builder\CategoryBuilder;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;

final readonly class Lister implements ListerInterface
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
        private CategoryBuilder $builder,
    ) {
    }

    #[\Override]
    public function __invoke(ListCriteria $criteria): CategoriesListData
    {
        // For now, we'll use findAll and filter/paginate in memory
        // In a real implementation, this would be optimized at the repository level
        $allCategories = $this->repository->findAll();

        // Filter by parent if specified
        if ($criteria->parentId instanceof \App\BlogContext\Domain\Shared\ValueObject\CategoryId) {
            $allCategories = array_filter(
                $allCategories,
                fn ($category) => $category->parentId?->getValue() === $criteria->parentId->getValue()
            );
        }

        $total = count($allCategories);

        // Apply pagination
        $categories = array_slice(
            $allCategories,
            $criteria->offset(),
            $criteria->limit
        );

        // Build domain models
        $domainCategories = array_map(
            fn ($category) => $this->builder->build(
                id: $category->id,
                name: $category->name,
                slug: $category->slug,
                description: $category->description,
                parentId: $category->parentId,
                order: $category->order,
                createdAt: $category->createdAt,
                updatedAt: $category->updatedAt,
            ),
            $categories
        );

        return new CategoriesListData(
            categories: $domainCategories,
            total: $total,
            page: $criteria->page,
            limit: $criteria->limit,
        );
    }
}
