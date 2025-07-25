<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategory;

use App\BlogContext\Domain\Shared\Model\Category;
use App\BlogContext\Domain\Shared\Repository\CategoryRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class Handler
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

        return new View(
            id: $category->id->getValue(),
            name: $category->name->getValue(),
            slug: $category->slug->getValue(),
            description: $category->description->getValue(),
            parentId: $category->parentId?->getValue(),
            order: $category->order->getValue(),
            createdAt: $category->createdAt->format(\DateTimeInterface::ATOM),
            updatedAt: $category->updatedAt->format(\DateTimeInterface::ATOM),
        );
    }
}
