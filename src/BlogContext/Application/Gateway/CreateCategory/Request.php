<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateCategory;

use App\Shared\Application\Gateway\GatewayRequest;

final readonly class Request implements GatewayRequest
{
    public function __construct(
        public string $name,
        public string $slug,
        public string|null $parentId = null,
        public \DateTimeImmutable|null $createdAt = null,
    ) {
        if ('' === trim($this->name)) {
            throw new \InvalidArgumentException('Category name cannot be empty');
        }

        if ('' === trim($this->slug)) {
            throw new \InvalidArgumentException('Category slug cannot be empty');
        }
    }

    public static function fromData(array $data): self
    {
        return new self(
            name: $data['name'] ?? throw new \InvalidArgumentException('Name is required'),
            slug: $data['slug'] ?? throw new \InvalidArgumentException('Slug is required'),
            parentId: $data['parentId'] ?? null,
            createdAt: isset($data['createdAt'])
                ? new \DateTimeImmutable($data['createdAt'])
                : null,
        );
    }

    public function data(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'parentId' => $this->parentId,
            'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
