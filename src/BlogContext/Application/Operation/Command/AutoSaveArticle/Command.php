<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\AutoSaveArticle;

final readonly class Command
{
    public function __construct(
        public string $articleId,
        public string $title,
        public string $content,
    ) {
    }
}
