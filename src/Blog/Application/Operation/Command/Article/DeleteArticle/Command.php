<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Command\Article\DeleteArticle;

final readonly class Command
{
    public function __construct(
        public string $articleId,
        public string $deletedBy,
    ) {
        if ('' === $this->articleId) {
            throw new \InvalidArgumentException('Article ID cannot be empty');
        }

        if ('' === $this->deletedBy) {
            throw new \InvalidArgumentException('DeletedBy cannot be empty');
        }
    }
}
