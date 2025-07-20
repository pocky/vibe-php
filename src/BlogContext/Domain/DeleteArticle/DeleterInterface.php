<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\DeleteArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

interface DeleterInterface
{
    public function __invoke(ArticleId $articleId, string $deletedBy): Model\Article;
}
