<?php

declare(strict_types=1);

namespace App\BlogContext\Domain\CreateArticle;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;

interface CreatorInterface
{
    public function __invoke(
        ArticleId $articleId,
        Title $title,
        Content $content,
        Slug $slug,
        string $authorId,
    ): Model\Article;
}
