<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\ApproveArticle;

final readonly class Command
{
    public function __construct(
        public string $articleId,
        public string $reviewerId,
        public string|null $reason = null,
    ) {
    }
}
