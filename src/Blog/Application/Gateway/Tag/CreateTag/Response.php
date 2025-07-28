<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\CreateTag;

final readonly class Response
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
