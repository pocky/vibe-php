<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateArticle;

final readonly class Command
{
    public function __construct(
        public string $articleId,
        public string|null $title = null,
        public string|null $content = null,
        public string|null $slug = null,
    ) {
        if ('' === $this->articleId) {
            throw new \InvalidArgumentException('Article ID cannot be empty');
        }
    }
}
