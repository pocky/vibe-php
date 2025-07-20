<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\GetArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

interface GetterInterface
{
    public function __invoke(ArticleId $articleId): Model\Article;
}
