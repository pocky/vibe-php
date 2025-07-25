<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteCategory;

use App\BlogContext\Domain\DeleteCategory\Builder\CategoryBuilder;
use App\BlogContext\Domain\DeleteCategory\Event\CategoryDeleted;
use App\BlogContext\Domain\DeleteCategory\Exception\CategoryNotFound;
use App\BlogContext\Domain\DeleteCategory\Model\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;

final readonly class Deleter implements DeleterInterface
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
        private CategoryBuilder $builder,
    ) {
    }

    #[\Override]
    public function __invoke(CategoryId $categoryId): Category
    {
        // Find the category
        $category = $this->repository->findById($categoryId);
        if (!$category instanceof \App\BlogContext\Domain\Shared\Model\Category) {
            throw CategoryNotFound::withId($categoryId);
        }

        // Business rule: Cannot delete category with articles
        $articleCount = $this->repository->countArticlesByCategory($categoryId);
        if (0 < $articleCount) {
            throw new \InvalidArgumentException(sprintf('Cannot delete category with %d articles', $articleCount));
        }

        // Business rule: Cannot delete category with children
        $children = $this->repository->findByParentId($categoryId);
        if (0 < count($children)) {
            throw new \InvalidArgumentException('Cannot delete category with child categories');
        }

        // Build domain model
        $domainCategory = $this->builder->build(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            parentId: $category->parentId,
            order: $category->order,
            createdAt: $category->createdAt,
            updatedAt: $category->updatedAt,
        );

        // Create domain event
        $event = new CategoryDeleted(
            categoryId: $categoryId->getValue(),
            name: $category->name->getValue(),
            deletedAt: new \DateTimeImmutable(),
        );

        // Attach event to category
        $categoryWithEvents = $domainCategory->withEvents([$event]);

        // Delete the category
        $this->repository->delete($categoryWithEvents);

        // Return category with unreleased events for Application layer to handle
        return $categoryWithEvents;
    }
}
