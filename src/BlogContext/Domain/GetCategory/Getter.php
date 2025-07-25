<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategory;

use App\BlogContext\Domain\GetCategory\Builder\CategoryBuilder;
use App\BlogContext\Domain\GetCategory\Exception\CategoryNotFound;
use App\BlogContext\Domain\GetCategory\Model\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final readonly class Getter implements GetterInterface
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
        private CategoryBuilder $builder,
    ) {
    }

    #[\Override]
    public function __invoke(CategoryId $categoryId): Category
    {
        $category = $this->repository->findById($categoryId);

        if (!$category instanceof \App\BlogContext\Domain\Shared\Model\Category) {
            throw CategoryNotFound::withId($categoryId);
        }

        return $this->builder->build(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            parentId: $category->parentId,
            order: $category->order,
            createdAt: $category->createdAt,
            updatedAt: $category->updatedAt,
        );
    }
}
