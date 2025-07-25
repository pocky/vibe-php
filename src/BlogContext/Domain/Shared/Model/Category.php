<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\Shared\Model;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;

final readonly class Category
{
    public function __construct(
        public CategoryId $id,
        public CategoryName $name,
        public CategorySlug $slug,
        public Description $description,
        public CategoryId|null $parentId,
        public Order $order,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function isRoot(): bool
    {
        return !$this->parentId instanceof CategoryId;
    }

    public function hasParent(): bool
    {
        return $this->parentId instanceof CategoryId;
    }
}
