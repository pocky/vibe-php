<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetArticle;

use App\BlogContext\Domain\GetArticle\GetterInterface;
use App\BlogContext\Domain\GetArticle\Model\Article;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;

final readonly class Handler implements HandlerInterface
{
    public function __construct(
        private GetterInterface $getter,
    ) {
    }

    public function __invoke(Query $query): Article
    {
        $articleId = new ArticleId($query->id);

        return ($this->getter)($articleId);
    }
}
