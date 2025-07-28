<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Category\ListCategories;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;

final readonly class Handler
{
    public function __construct(
        private CategoryReadRepositoryInterface $categoryReadRepository,
    ) {
    }

    public function __invoke(Query $query): View
    {
        if (null !== $query->parentId && '' !== $query->parentId && '0' !== $query->parentId) {
            new CategoryId($query->parentId);
        }

        // For now, return a simple view - this would need proper implementation
        // with pagination, filtering, etc. from the repository
        $categories = $this->categoryReadRepository->findAll();

        return new View(
            categories: array_map(
                fn ($category): CategoryView => new CategoryView(
                    id: $category->id->getValue(),
                    name: $category->name->getValue(),
                    slug: $category->slug->getValue(),
                    description: $category->description->getValue(),
                    parentId: $category->parentId?->getValue(),
                    order: $category->order->getValue(),
                    createdAt: $category->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
                    updatedAt: $category->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                ),
                $categories,
            ),
            total: count($categories),
            page: $query->page,
            limit: $query->limit,
        );
    }
}
