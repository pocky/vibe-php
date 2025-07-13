<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\AutoSaveArticle;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Content, Title};

final readonly class Command
{
    public function __construct(
        public ArticleId $articleId,
        public Title $title,
        public Content $content,
    ) {
    }
}
