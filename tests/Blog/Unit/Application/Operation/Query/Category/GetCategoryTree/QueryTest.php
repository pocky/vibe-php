<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\GetCategoryTree;

use App\Blog\Application\Operation\Query\Category\GetCategoryTree\Query;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    #[Test]
    public function validQuery_constructsSuccessfully(): void
    {
        $query = new Query();

        // The query doesn't need any parameters since we're building the entire tree
        $this->assertInstanceOf(Query::class, $query);
    }
}
