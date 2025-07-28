<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\UpdateTag;

final readonly class Response
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
