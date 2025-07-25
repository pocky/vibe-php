<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\UpdateCategory;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string|null $description,
        public string|null $parentId,
        public int $order,
        public string $updatedAt,
    ) {
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
            'updatedAt' => $this->updatedAt,
        ];
    }
}
