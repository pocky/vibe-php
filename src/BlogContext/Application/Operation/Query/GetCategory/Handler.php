<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategory;

use App\BlogContext\Domain\CreateCategory\DataPersister\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
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
        $categoryId = new CategoryId($query->id);
        $category = $this->repository->findById($categoryId);

        if (!$category instanceof Category) {
            throw new \RuntimeException('Category not found');
        }

        // Get children categories
        $children = $this->repository->findChildrenByParentId($categoryId);
        $childrenViews = array_map(
            fn (Category $child) => new CategoryView(
                id: $child->id()->getValue(),
                name: $child->name()->getValue(),
                slug: $child->slug()->getValue(),
                path: $child->path()->getValue(),
                level: $child->path()->getDepth(),
                articleCount: 0, // TODO: Implement article counting
            ),
            $children
        );

        return new View(
            id: $category->id()->getValue(),
            name: $category->name()->getValue(),
            slug: $category->slug()->getValue(),
            path: $category->path()->getValue(),
            parentId: $category->parentId()?->getValue(),
            level: $category->path()->getDepth(),
            articleCount: 0, // TODO: Implement article counting
            children: $childrenViews,
            createdAt: $category->createdAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $category->updatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
