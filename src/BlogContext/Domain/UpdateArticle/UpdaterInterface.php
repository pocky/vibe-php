<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle;

use App\BlogContext\Domain\Shared\ValueObject\{ArticleId, Content, Title};
use App\BlogContext\Domain\UpdateArticle\DataPersister\Article;

interface UpdaterInterface
{
    public function __invoke(ArticleId $articleId, Title $title, Content $content): Article;
}
