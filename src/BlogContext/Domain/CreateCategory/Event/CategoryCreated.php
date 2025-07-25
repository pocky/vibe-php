<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory\Event;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use App\BlogContext\Domain\Shared\ValueObject\Description;
use App\BlogContext\Domain\Shared\ValueObject\Order;

final readonly class CategoryCreated
{
    public function __construct(
        private CategoryId $categoryId,
        private CategoryName $name,
        private CategorySlug $slug,
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
        return $this->name;
    }

    public function slug(): CategorySlug
    {
        return $this->slug;
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
        return 'BlogContext.Category.Created';
    }

    public function aggregateId(): string
    {
        return $this->categoryId->getValue();
    }

    public function toArray(): array
    {
        return [
            'categoryId' => $this->categoryId->getValue(),
            'name' => $this->name->getValue(),
            'slug' => $this->slug->getValue(),
            'description' => $this->description->getValue(),
            'parentId' => $this->parentId?->getValue(),
            'order' => $this->order->getValue(),
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
