<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\PublishArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final readonly class Command
{
    public function __construct(
        public ArticleId $articleId,
    ) {
    }
}
