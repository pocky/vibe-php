<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\GetCategory;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string|null $description,
        public string|null $parentId,
        public int $order,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public function data(): array
    {
        return [
            'category' => [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'parentId' => $this->parentId,
                'order' => $this->order,
                'createdAt' => $this->createdAt,
                'updatedAt' => $this->updatedAt,
            ],
        ];
    }
}
