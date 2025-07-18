<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateCategory\Event;

use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use App\BlogContext\Domain\Shared\ValueObject\CategoryPath;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;

final readonly class CategoryCreated
{
    public function __construct(
        private CategoryId $categoryId,
        private CategoryName $name,
        private CategorySlug $slug,
        private CategoryPath $path,
        private CategoryId|null $parentId,
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

    public function path(): CategoryPath
    {
        return $this->path;
    }

    public function parentId(): CategoryId|null
    {
        return $this->parentId;
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
            'name' => $this->name->getValue(),
            'slug' => $this->slug->getValue(),
            'path' => $this->path->getValue(),
            'parentId' => $this->parentId?->getValue(),
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'eventType' => $this->eventType(),
        ];
    }
}
