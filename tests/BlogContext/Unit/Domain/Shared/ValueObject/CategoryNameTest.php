<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
use App\BlogContext\Domain\Shared\ValueObject\CategoryName;
use PHPUnit\Framework\TestCase;

final class CategoryNameTest extends TestCase
{
    public function testValidCategoryName(): void
    {
        $name = new CategoryName('Technology');

        $this->assertEquals('Technology', $name->getValue());
    }

    public function testValidNamesVariations(): void
    {
        $validNames = [
            'Technology',
            'Home & Garden',
            'Books & Literature',
            'Ai', // 2 characters minimum
            str_repeat('a', 100), // 100 characters maximum
        ];

        foreach ($validNames as $name) {
            $categoryName = new CategoryName($name);
            $this->assertEquals($name, $categoryName->getValue());
        }
    }

    public function testEmptyNameThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.name.empty');

        new CategoryName('');
    }

    public function testWhitespaceOnlyNameThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.name.empty');

        new CategoryName('   ');
    }

    public function testTooShortNameThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.name.too_short');

        new CategoryName('A');
    }

    public function testTooLongNameThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.name.too_long');

        new CategoryName(str_repeat('a', 101));
    }

    public function testEquals(): void
    {
        $name1 = new CategoryName('Technology');
        $name2 = new CategoryName('Technology');
        $name3 = new CategoryName('Science');

        $this->assertTrue($name1->equals($name2));
        $this->assertFalse($name1->equals($name3));
    }
}
