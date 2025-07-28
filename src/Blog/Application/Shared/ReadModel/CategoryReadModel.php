<?php

declare(strict_types=1);

namespace App\Blog\Application\Shared\ReadModel;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Timestamps;

/**
 * Read model for category data.
 * Used for query operations and represents the current state of a category.
 */
final readonly class CategoryReadModel
{
    public function __construct(
        public CategoryId $id,
        public CategoryName $name,
        public CategorySlug $slug,
        public Description $description,
        public CategoryId|null $parentId,
        public Order $order,
        public Timestamps $timestamps,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->getValue(),
            'name' => $this->name->getValue(),
            'slug' => $this->slug->getValue(),
            'description' => $this->description->getValue(),
            'parentId' => $this->parentId?->getValue(),
            'order' => $this->order->getValue(),
            'createdAt' => $this->timestamps->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->timestamps->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
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
