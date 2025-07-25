<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateCategory;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string|null $description,
        public string|null $parentId,
        public int $order,
    ) {
        if ('' === $categoryId || '0' === $categoryId) {
            throw new \InvalidArgumentException('Category ID cannot be empty');
        }
        if ('' === $name || '0' === $name) {
            throw new \InvalidArgumentException('Category name cannot be empty');
        }
        if ('' === $slug || '0' === $slug) {
            throw new \InvalidArgumentException('Category slug cannot be empty');
        }
        if (0 > $order) {
            throw new \InvalidArgumentException('Order must be non-negative');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            categoryId: $data['categoryId'] ?? '',
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? null,
            parentId: $data['parentId'] ?? null,
            order: (int) ($data['order'] ?? 0),
        );
    }

    public function data(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parentId' => $this->parentId,
            'order' => $this->order,
        ];
    }
}
