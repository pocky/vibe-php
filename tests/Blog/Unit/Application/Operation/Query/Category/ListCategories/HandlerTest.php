<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\ListCategories;

use App\Blog\Application\Operation\Query\Category\ListCategories\CategoryView;
use App\Blog\Application\Operation\Query\Category\ListCategories\Handler;
use App\Blog\Application\Operation\Query\Category\ListCategories\Query;
use App\Blog\Application\Operation\Query\Category\ListCategories\View;
use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\Repository\CategoryReadRepositoryInterface;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private CategoryReadRepositoryInterface&MockObject $categoryReadRepository;

    private Handler $handler;

    protected function setUp(): void
    {
        $this->categoryReadRepository = $this->createMock(CategoryReadRepositoryInterface::class);
        $this->handler = new Handler($this->categoryReadRepository);
    }

    #[Test]
    public function validQuery_withNoCategories_returnsEmptyView(): void
    {
        $query = new Query();

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(View::class, $result);
        $this->assertEmpty($result->categories);
        $this->assertSame(0, $result->total);
        $this->assertSame(1, $result->page);
        $this->assertSame(20, $result->limit);
    }

    #[Test]
    public function validQuery_withCategories_returnsView(): void
    {
        $query = new Query(page: 2, limit: 10);

        $categoryReadModel = new CategoryReadModel(
            id: new CategoryId('category-123'),
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Technology articles'),
            parentId: null,
            order: new Order(10),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2024-01-01'),
                updatedAt: new \DateTimeImmutable('2024-01-02')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$categoryReadModel]);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(View::class, $result);
        $this->assertCount(1, $result->categories);
        $this->assertSame(1, $result->total);
        $this->assertSame(2, $result->page);
        $this->assertSame(10, $result->limit);

        $categoryView = $result->categories[0];
        $this->assertInstanceOf(CategoryView::class, $categoryView);
        $this->assertSame('category-123', $categoryView->id);
        $this->assertSame('Technology', $categoryView->name);
        $this->assertSame('technology', $categoryView->slug);
        $this->assertSame('Technology articles', $categoryView->description);
        $this->assertNull($categoryView->parentId);
        $this->assertSame(10, $categoryView->order);
        $this->assertSame('2024-01-01T00:00:00+00:00', $categoryView->createdAt);
        $this->assertSame('2024-01-02T00:00:00+00:00', $categoryView->updatedAt);
    }
}
