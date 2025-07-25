<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteCategory\Event;

final readonly class CategoryDeleted
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public \DateTimeImmutable $deletedAt,
    ) {
    }

    public static function eventType(): string
    {
        return 'blog.category.deleted';
    }

    public function aggregateId(): string
    {
        return $this->categoryId;
    }
}
