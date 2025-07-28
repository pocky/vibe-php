<?php

declare(strict_types=1);

namespace App\Blog\Application\Operation\Query\Article\GetArticle;

use App\Blog\Application\Shared\ReadModel\ArticleReadModel;
use App\Blog\Domain\Article\ArticleGetter;
use App\Blog\Domain\Article\Shared\Identifier\ArticleId;

final readonly class Handler
{
    public function __construct(
        private ArticleGetter $getter,
    ) {
    }

    public function __invoke(Query $query): ArticleReadModel
    {
        $articleId = new ArticleId($query->id);

        return ($this->getter)($articleId);
    }
}
