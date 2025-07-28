<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\ListCategories;

use App\Blog\Application\Operation\Query\Category\ListCategories\Query;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    #[Test]
    public function validQuery_withDefaults_constructsSuccessfully(): void
    {
        $query = new Query();

        $this->assertSame(1, $query->page);
        $this->assertSame(20, $query->limit);
        $this->assertNull($query->sortBy);
        $this->assertSame('asc', $query->sortOrder);
        $this->assertNull($query->parentId);
    }

    #[Test]
    public function validQuery_withCustomParameters_constructsSuccessfully(): void
    {
        $query = new Query(
            page: 2,
            limit: 10,
            sortBy: 'name',
            sortOrder: 'desc',
            parentId: 'parent-123'
        );

        $this->assertSame(2, $query->page);
        $this->assertSame(10, $query->limit);
        $this->assertSame('name', $query->sortBy);
        $this->assertSame('desc', $query->sortOrder);
        $this->assertSame('parent-123', $query->parentId);
    }

    #[Test]
    public function invalidQuery_zeroPage_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be greater than 0');

        new Query(page: 0);
    }

    #[Test]
    public function invalidQuery_negativePage_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be greater than 0');

        new Query(page: -1);
    }

    #[Test]
    public function invalidQuery_zeroLimit_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');

        new Query(limit: 0);
    }

    #[Test]
    public function invalidQuery_highLimit_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');

        new Query(limit: 101);
    }

    #[Test]
    public function invalidQuery_invalidSortOrder_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sort order must be "asc" or "desc"');

        new Query(sortOrder: 'invalid');
    }
}
