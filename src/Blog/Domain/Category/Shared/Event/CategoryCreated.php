<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Event;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;

final readonly class CategoryCreated
{
    public function __construct(
        private CategoryId $categoryId,
        private CategoryName $categoryName,
        private CategorySlug $categorySlug,
        private Description $description,
        private CategoryId|null $parentId,
        private Order $order,
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public function categoryId(): CategoryId
    {
        return $this->categoryId;
    }

    public function name(): CategoryName
    {
        return $this->categoryName;
    }

    public function slug(): CategorySlug
    {
        return $this->categorySlug;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function parentId(): CategoryId|null
    {
        return $this->parentId;
    }

    public function order(): Order
    {
        return $this->order;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function eventType(): string
    {
        return 'Blog.Category.Created';
    }

    public function aggregateId(): string
    {
        return $this->categoryId->getValue();
    }

    public function toArray(): array
    {
        return [
            'categoryId' => $this->categoryId->getValue(),
            'name' => $this->categoryName->getValue(),
            'slug' => $this->categorySlug->getValue(),
            'description' => $this->description->getValue(),
            'parentId' => $this->parentId?->getValue(),
            'order' => $this->order->getValue(),
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
