<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Command\UpdateArticle;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, ArticleStatus, Content, Slug, Title};

final readonly class Command
{
    public function __construct(
        public ArticleId $articleId,
        public Title $title,
        public Content $content,
        public Slug $slug,
        public ArticleStatus $status,
    ) {
    }
}
