<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Tag\SearchTags;

final readonly class View
{
    /**
     * @param array<ItemView> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {
    }

    public function toArray(): array
    {
        return [
            'items' => array_map(fn (ItemView $itemView): array => $itemView->toArray(), $this->items),
            'total' => $this->total,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}

final readonly class ItemView
{
    public function __construct(
        public string $id,
        // TODO: Add other fields
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            // TODO: Add other fields
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
