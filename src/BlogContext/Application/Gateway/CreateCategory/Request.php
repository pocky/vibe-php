<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateCategory;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $name,
        public string $description,
        public string|null $parentCategoryId = null,
        public int $order = 0,
    ) {
        if ('' === trim($this->name)) {
            throw new \InvalidArgumentException('Name is required');
        }

        if ('' === trim($this->description)) {
            throw new \InvalidArgumentException('Description is required');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            description: $data['description'] ?? '',
            parentCategoryId: $data['parentCategoryId'] ?? null,
            order: (int) ($data['order'] ?? 0),
        );
    }

    public function data(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'parentCategoryId' => $this->parentCategoryId,
            'order' => $this->order,
        ];
    }
}
