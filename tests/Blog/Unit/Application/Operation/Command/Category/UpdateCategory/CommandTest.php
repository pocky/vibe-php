<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Category\UpdateCategory;

use App\Blog\Application\Operation\Command\Category\UpdateCategory\Command;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    #[Test]
    public function validCommand_withAllFields_constructsSuccessfully(): void
    {
        $command = new Command(
            categoryId: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles',
            parentId: 'parent-456',
            order: 10
        );

        $this->assertSame('category-123', $command->categoryId);
        $this->assertSame('Technology', $command->name);
        $this->assertSame('technology', $command->slug);
        $this->assertSame('Technology related articles', $command->description);
        $this->assertSame('parent-456', $command->parentId);
        $this->assertSame(10, $command->order);
    }

    #[Test]
    public function validCommand_withOptionalFields_constructsSuccessfully(): void
    {
        $command = new Command(
            categoryId: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles'
        );

        $this->assertSame('category-123', $command->categoryId);
        $this->assertSame('Technology', $command->name);
        $this->assertSame('technology', $command->slug);
        $this->assertSame('Technology related articles', $command->description);
        $this->assertNull($command->parentId);
        $this->assertNull($command->order);
        $this->assertFalse($command->clearParent);
    }

    #[Test]
    public function emptyCategpryId_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Category ID cannot be empty');

        new Command(
            categoryId: '',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles'
        );
    }

    #[Test]
    public function emptyCategoryName_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Name cannot be empty');

        new Command(
            categoryId: 'category-123',
            name: '',
            slug: 'technology',
            description: 'Technology related articles'
        );
    }

    #[Test]
    public function emptySlug_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug cannot be empty');

        new Command(
            categoryId: 'category-123',
            name: 'Technology',
            slug: '',
            description: 'Technology related articles'
        );
    }

    #[Test]
    public function emptyDescription_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Description cannot be empty');

        new Command(
            categoryId: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: ''
        );
    }

    #[Test]
    public function emptyParentId_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parent ID cannot be empty string');

        new Command(
            categoryId: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles',
            parentId: ''
        );
    }

    #[Test]
    public function negativeOrder_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Order must be non-negative');

        new Command(
            categoryId: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles',
            order: -1
        );
    }

    #[Test]
    public function clearParentFlag_setsCorrectly(): void
    {
        $command = new Command(
            categoryId: 'category-123',
            name: 'Technology',
            slug: 'technology',
            description: 'Technology related articles',
            clearParent: true
        );

        $this->assertTrue($command->clearParent);
    }
}
