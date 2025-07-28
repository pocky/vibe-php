<?php

declare(strict_types=1);

namespace App\Blog\Application\Gateway\Tag\GetTag;

final readonly class Response
{
    public function __construct(
        public array $tag,
    ) {
    }

    public function data(): array
    {
        return [
            'tag' => $this->tag,
        ];
    }
}
