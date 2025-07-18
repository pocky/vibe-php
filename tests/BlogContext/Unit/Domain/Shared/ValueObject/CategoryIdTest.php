<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
use App\BlogContext\Domain\Shared\ValueObject\CategoryId;
use PHPUnit\Framework\TestCase;

final class CategoryIdTest extends TestCase
{
    public function testValidCategoryId(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $categoryId = new CategoryId($uuid);

        $this->assertEquals($uuid, $categoryId->getValue());
        $this->assertEquals($uuid, $categoryId->toString());
    }

    public function testEmptyIdThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.id.invalid_uuid');

        new CategoryId('');
    }

    public function testInvalidUuidThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.id.invalid_uuid');

        new CategoryId('invalid-uuid');
    }

    public function testEquals(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $categoryId1 = new CategoryId($uuid);
        $categoryId2 = new CategoryId($uuid);
        $categoryId3 = new CategoryId('550e8400-e29b-41d4-a716-446655440001');

        $this->assertTrue($categoryId1->equals($categoryId2));
        $this->assertFalse($categoryId1->equals($categoryId3));
    }
}
