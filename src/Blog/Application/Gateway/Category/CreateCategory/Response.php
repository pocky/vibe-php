<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Category\CreateCategory;

final readonly class Response
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $description,
        public string|null $parentId,
        public int|null $order,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parentId' => $this->parentId,
            'order' => $this->order,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
