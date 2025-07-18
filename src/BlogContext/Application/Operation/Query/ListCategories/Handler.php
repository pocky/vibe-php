<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\ListCategories;

use App\BlogContext\Domain\CreateCategory\DataPersister\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class Handler
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        // Get all categories ordered by path (hierarchical order)
        $categories = $this->repository->findAll();

        // Transform to view models
        $categoryViews = array_map(
            fn (Category $category) => new CategoryView(
                id: $category->id()->getValue(),
                name: $category->name()->getValue(),
                slug: $category->slug()->getValue(),
                path: $category->path()->getValue(),
                parentId: $category->parentId()?->getValue(),
                level: $category->path()->getDepth(),
                articleCount: 0, // TODO: Implement article counting
                createdAt: $category->createdAt()->format(\DateTimeInterface::ATOM),
                updatedAt: $category->updatedAt()->format(\DateTimeInterface::ATOM),
            ),
            $categories
        );

        return new View(
            categories: $categoryViews,
            total: count($categoryViews),
        );
    }
}
