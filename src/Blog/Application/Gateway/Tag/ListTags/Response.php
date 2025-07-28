<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\ListTags;

final readonly class Response
{
    public function __construct(
        public array $tags,
        public int $total,
    ) {
    }

    public function data(): array
    {
        return [
            'tags' => $this->tags,
            'total' => $this->total,
        ];
    }
}
