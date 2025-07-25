<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetAuthorArticles;

interface HandlerInterface
{
    public function __invoke(Query $query): View;
}
