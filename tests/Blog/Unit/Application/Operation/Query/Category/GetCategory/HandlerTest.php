<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\GetCategory;

use App\Blog\Application\Operation\Query\Category\GetCategory\Handler;
use App\Blog\Application\Operation\Query\Category\GetCategory\Query;
use App\Blog\Application\Operation\Query\Category\GetCategory\View;
use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\CategoryGetter;
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

        // Use real CategoryGetter with mocked repository
        $categoryGetter = new CategoryGetter($this->categoryReadRepository);

        $this->handler = new Handler($categoryGetter);
    }

    #[Test]
    public function validQuery_withId_returnsView(): void
    {
        $query = new Query(categoryId: 'category-123');

        $categoryReadModel = new CategoryReadModel(
            id: new CategoryId('category-123'),
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Technology articles'),
            parentId: null,
            order: new Order(10),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2024-01-01'),
                updatedAt: new \DateTimeImmutable('2024-01-01')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn (CategoryId $id): bool => 'category-123' === $id->getValue()))
            ->willReturn($categoryReadModel);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(View::class, $result);
        $this->assertSame($categoryReadModel, $result->category);
    }

    #[Test]
    public function validQuery_withSlug_returnsView(): void
    {
        $query = new Query(categorySlug: 'technology');

        $categoryReadModel = new CategoryReadModel(
            id: new CategoryId('category-123'),
            name: new CategoryName('Technology'),
            slug: new CategorySlug('technology'),
            description: new Description('Technology articles'),
            parentId: null,
            order: new Order(10),
            timestamps: new Timestamps(
                createdAt: new \DateTimeImmutable('2024-01-01'),
                updatedAt: new \DateTimeImmutable('2024-01-01')
            )
        );

        $this->categoryReadRepository
            ->expects($this->once())
            ->method('findBySlug')
            ->with($this->callback(fn (CategorySlug $slug): bool => 'technology' === $slug->getValue()))
            ->willReturn($categoryReadModel);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(View::class, $result);
        $this->assertSame($categoryReadModel, $result->category);
    }
}
