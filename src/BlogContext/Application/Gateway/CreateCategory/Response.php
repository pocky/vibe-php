<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Gateway\CreateCategory;

use App\Shared\Application\Gateway\GatewayResponse;

final readonly class Response implements GatewayResponse
{
    public function __construct(
        public string $categoryId,
        public string $name,
        public string $slug,
        public string $path,
        public string|null $parentId,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    public function data(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'name' => $this->name,
            'slug' => $this->slug,
            'path' => $this->path,
            'parentId' => $this->parentId,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
