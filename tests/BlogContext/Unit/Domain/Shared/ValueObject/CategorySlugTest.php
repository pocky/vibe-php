<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
use App\BlogContext\Domain\Shared\ValueObject\CategorySlug;
use PHPUnit\Framework\TestCase;

final class CategorySlugTest extends TestCase
{
    public function testValidCategorySlug(): void
    {
        $slug = new CategorySlug('technology');

        $this->assertEquals('technology', $slug->getValue());
    }

    public function testValidSlugsVariations(): void
    {
        $validSlugs = [
            'technology',
            'home-garden',
            'books-literature',
            'web-development',
            'abc', // 3 characters minimum
            str_repeat('a', 250), // 250 characters maximum
        ];

        foreach ($validSlugs as $slug) {
            $categorySlug = new CategorySlug($slug);
            $this->assertEquals($slug, $categorySlug->getValue());
        }
    }

    public function testEmptySlugThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.slug.empty');

        new CategorySlug('');
    }

    public function testTooShortSlugThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.slug.too_short');

        new CategorySlug('ab');
    }

    public function testTooLongSlugThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.slug.too_long');

        new CategorySlug(str_repeat('a', 251));
    }

    public function testInvalidCharactersThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.category.slug.invalid_format');

        new CategorySlug('Technology & Science');
    }

    public function testInvalidSlugsVariations(): void
    {
        $invalidSlugs = [
            'Technology', // uppercase
            'tech_nology', // underscore
            'tech nology', // space
            'tech@nology', // special characters
            'tech.nology', // dot
            'tech/nology', // slash
        ];

        foreach ($invalidSlugs as $slug) {
            try {
                new CategorySlug($slug);
                $this->fail("Expected ValidationException for slug: {$slug}");
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testEquals(): void
    {
        $slug1 = new CategorySlug('technology');
        $slug2 = new CategorySlug('technology');
        $slug3 = new CategorySlug('science');

        $this->assertTrue($slug1->equals($slug2));
        $this->assertFalse($slug1->equals($slug3));
    }
}
