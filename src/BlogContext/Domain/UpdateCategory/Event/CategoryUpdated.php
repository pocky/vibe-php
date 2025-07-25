<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateCategory\Event;

final readonly class CategoryUpdated
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string $description,
        public string|null $parentId,
        public int $order,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function eventType(): string
    {
        return 'blog.category.updated';
    }

    public function aggregateId(): string
    {
        return $this->categoryId;
    }
}
