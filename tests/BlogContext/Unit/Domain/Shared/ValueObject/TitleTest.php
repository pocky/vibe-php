<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;

final class TitleTest extends TestCase
{
    public function testCreateValidTitle(): void
    {
        $title = new Title('Valid Article Title');

        $this->assertSame('Valid Article Title', $title->getValue());
    }

    public function testTitleEquality(): void
    {
        $title1 = new Title('Same Title');
        $title2 = new Title('Same Title');
        $title3 = new Title('Different Title');

        $this->assertTrue($title1->equals($title2));
        $this->assertFalse($title1->equals($title3));
    }

    public function testRejectEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty');

        new Title('');
    }

    public function testRejectWhitespaceOnlyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty');

        new Title('   ');
    }

    public function testRejectTooShortTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title must be at least 5 characters');

        new Title('Hi');
    }

    public function testAcceptMinimumLengthTitle(): void
    {
        $title = new Title('Hello');

        $this->assertSame('Hello', $title->getValue());
    }

    public function testRejectTooLongTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot exceed 200 characters');

        new Title(str_repeat('a', 201));
    }

    public function testAcceptMaximumLengthTitle(): void
    {
        $title = new Title(str_repeat('a', 200));

        $this->assertSame(str_repeat('a', 200), $title->getValue());
    }

    public function testTrimWhitespaceButKeepSpaces(): void
    {
        $title = new Title('  Valid Title With Spaces  ');

        $this->assertSame('  Valid Title With Spaces  ', $title->getValue());
    }
}
