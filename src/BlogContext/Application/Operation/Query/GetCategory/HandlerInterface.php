<?php

declare(strict_types=1);

namespace App\BlogContext\Application\Operation\Query\GetCategory;

interface HandlerInterface
{
    public function __invoke(Query $query): View;
}
