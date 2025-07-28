<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Application\Operation\Command\Category\DeleteCategory;

use App\Blog\Application\Operation\Command\Category\DeleteCategory\Command;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CommandTest extends TestCase
{
    #[Test]
    public function validCommand_constructsSuccessfully(): void
    {
        $command = new Command(categoryId: 'category-123');

        $this->assertSame('category-123', $command->categoryId);
    }

    #[Test]
    public function emptyCategoryId_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Category ID cannot be empty');

        new Command(categoryId: '');
    }

    #[Test]
    public function whitespaceCategoryId_throwsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Category ID cannot be empty');

        new Command(categoryId: '   ');
    }
}
