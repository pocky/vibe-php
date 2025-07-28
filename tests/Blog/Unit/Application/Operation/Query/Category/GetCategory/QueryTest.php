<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\GetCategory;

use App\Blog\Application\Operation\Query\Category\GetCategory\Query;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class QueryTest extends TestCase
{
    #[Test]
    public function validQuery_withId_constructsSuccessfully(): void
    {
        $query = new Query(categoryId: 'category-123');

        $this->assertSame('category-123', $query->categoryId);
        $this->assertNull($query->categorySlug);
    }

    #[Test]
    public function validQuery_withSlug_constructsSuccessfully(): void
    {
        $query = new Query(categorySlug: 'technology');

        $this->assertNull($query->categoryId);
        $this->assertSame('technology', $query->categorySlug);
    }

    #[Test]
    public function validQuery_withBothIdAndSlug_constructsSuccessfully(): void
    {
        $query = new Query(categoryId: 'category-123', categorySlug: 'technology');

        $this->assertSame('category-123', $query->categoryId);
        $this->assertSame('technology', $query->categorySlug);
    }

    #[Test]
    public function invalidQuery_withBothEmpty_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Either categoryId or categorySlug must be provided');

        new Query();
    }

    #[Test]
    public function invalidQuery_emptyCategoryId_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Category ID cannot be empty');

        new Query(categoryId: '');
    }

    #[Test]
    public function invalidQuery_emptyCategorySlug_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Category slug cannot be empty');

        new Query(categorySlug: '');
    }
}
