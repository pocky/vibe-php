<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Query\Category\GetCategory;

use App\Blog\Application\Operation\Query\Category\GetCategory\View;
use App\Blog\Application\Shared\ReadModel\CategoryReadModel;
use App\Blog\Domain\Category\Shared\Identifier\CategoryId;
use App\Blog\Domain\Category\Shared\ValueObject\CategoryName;
use App\Blog\Domain\Category\Shared\ValueObject\CategorySlug;
use App\Blog\Domain\Shared\ValueObject\Description;
use App\Blog\Domain\Shared\ValueObject\Order;
use App\Blog\Domain\Shared\ValueObject\Timestamps;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    #[Test]
    public function validView_constructsSuccessfully(): void
    {
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

        $view = new View($categoryReadModel);

        $this->assertSame($categoryReadModel, $view->category);
    }
}
