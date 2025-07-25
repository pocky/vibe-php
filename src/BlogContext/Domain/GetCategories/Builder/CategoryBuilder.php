<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetCategories\Builder;

use App\BlogContext\Domain\GetCategories\Model\Category;
use App\BlogContext\Domain\Shared\Builder\CategoryBuilderInterface;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;

final class CategoryBuilder implements CategoryBuilderInterface
{
    public function build(
        CategoryId $id,
        CategoryName $name,
        CategorySlug $slug,
        Description $description,
        CategoryId|null $parentId,
        Order $order,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): Category {
        return new Category(
            id: $id,
            name: $name,
            slug: $slug,
            description: $description,
            parentId: $parentId,
            order: $order,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
