<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\UpdateArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;

interface UpdaterInterface
{
    public function __invoke(
        ArticleId $articleId,
        Title|null $title = null,
        Content|null $content = null,
        Slug|null $slug = null,
    ): Model\Article;
}
