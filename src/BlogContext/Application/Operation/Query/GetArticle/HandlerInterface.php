<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetArticle;

use App\BlogContext\Domain\GetArticle\Model\Article;

interface HandlerInterface
{
    public function __invoke(Query $query): Article;
}
