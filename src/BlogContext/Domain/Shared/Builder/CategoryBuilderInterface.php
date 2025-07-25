<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Builder;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;

interface CategoryBuilderInterface
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
    ): mixed;
}
