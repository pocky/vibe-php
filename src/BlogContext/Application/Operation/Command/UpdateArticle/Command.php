<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateArticle;

final readonly class Command
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $content,
    ) {
    }
}
