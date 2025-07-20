<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\CreateArticle;

final readonly class Command
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $content,
        public string $slug,
        public string $authorId,
        public string $status = 'draft',
    ) {
        if ('' === trim($this->articleId)) {
            throw new \InvalidArgumentException('Article ID cannot be empty');
        }

        if ('' === trim($this->title)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if ('' === trim($this->content)) {
            throw new \InvalidArgumentException('Content cannot be empty');
        }

        if ('' === trim($this->slug)) {
            throw new \InvalidArgumentException('Slug cannot be empty');
        }

        if ('' === trim($this->authorId)) {
            throw new \InvalidArgumentException('Author ID cannot be empty');
        }

        if (!in_array($this->status, ['draft', 'published'], true)) {
            throw new \InvalidArgumentException('Invalid status. Must be draft or published');
        }
    }
}
