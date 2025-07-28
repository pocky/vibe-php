<?php

declare(strict_types=1);

namespace App\Blog\Domain\Category\Shared\Event;

use App\Blog\Domain\Category\Shared\Identifier\CategoryId;

final readonly class CategoryDeleted
{
    public function __construct(
        private CategoryId $categoryId,
        private \DateTimeImmutable $deletedAt,
    ) {
    }

    public function categoryId(): CategoryId
    {
        return $this->categoryId;
    }

    public function deletedAt(): \DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function eventType(): string
    {
        return 'Blog.Category.Deleted';
    }

    public function aggregateId(): string
    {
        return $this->categoryId->getValue();
    }

    public function toArray(): array
    {
        return [
            'categoryId' => $this->categoryId->getValue(),
            'deletedAt' => $this->deletedAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
