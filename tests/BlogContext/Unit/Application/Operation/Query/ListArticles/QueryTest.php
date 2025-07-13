<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Query\ListArticles;

use App\BlogContext\Application\Operation\Query\ListArticles\Query;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    public function testQueryCreationWithDefaults(): void
    {
        $query = new Query();

        $this->assertEquals(1, $query->page);
        $this->assertEquals(20, $query->limit);
        $this->assertNull($query->status);
    }

    public function testQueryCreationWithCustomValues(): void
    {
        $query = new Query(page: 2, limit: 10, status: 'published');

        $this->assertEquals(2, $query->page);
        $this->assertEquals(10, $query->limit);
        $this->assertEquals('published', $query->status);
    }

    public function testQueryIsReadonly(): void
    {
        $query = new Query();

        // Should be readonly - attempting to modify should fail
        $this->expectException(\Error::class);
        $query->page = 999;
    }
}
