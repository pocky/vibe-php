<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\ValueObject\{Slug};
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    public function testCreateValidSlug(): void
    {
        $slug = new Slug('valid-article-slug');

        $this->assertSame('valid-article-slug', $slug->getValue());
    }

    public function testSlugEquality(): void
    {
        $slug1 = new Slug('same-slug');
        $slug2 = new Slug('same-slug');
        $slug3 = new Slug('different-slug');

        $this->assertTrue($slug1->equals($slug2));
        $this->assertFalse($slug1->equals($slug3));
    }

    public function testRejectEmptySlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug cannot be empty');

        new Slug('');
    }

    public function testRejectInvalidSlugFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid slug format');

        new Slug('Invalid Slug With Spaces');
    }

    public function testRejectSlugWithSpecialCharacters(): void
    {
        $invalidSlugs = [
            'slug-with-@-symbols',
            'slug_with_underscores',
            'slug with spaces',
            'slug!',
            'slug#test',
            'slug$test',
            'slug%test',
            'slug^test',
            'slug&test',
            'slug*test',
            'slug(test)',
            'slug+test',
            'slug=test',
            'slug[test]',
            'slug{test}',
            'slug|test',
            'slug\\test',
            'slug:test',
            'slug;test',
            'slug"test',
            "slug'test",
            'slug<test>',
            'slug,test',
            'slug.test',
            'slug?test',
            'slug/test',
        ];

        foreach ($invalidSlugs as $invalidSlug) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid slug format');

            new Slug($invalidSlug);
        }
    }

    public function testRejectSlugWithUppercase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid slug format');

        new Slug('Slug-With-Uppercase');
    }

    public function testRejectSlugWithConsecutiveHyphens(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid slug format');

        new Slug('slug--with--double-hyphens');
    }

    public function testRejectSlugStartingWithHyphen(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid slug format');

        new Slug('-starting-with-hyphen');
    }

    public function testRejectSlugEndingWithHyphen(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid slug format');

        new Slug('ending-with-hyphen-');
    }

    public function testAcceptSlugWithNumbers(): void
    {
        $slug = new Slug('article-with-123-numbers');

        $this->assertSame('article-with-123-numbers', $slug->getValue());
    }

    public function testAcceptSingleWordSlug(): void
    {
        $slug = new Slug('article');

        $this->assertSame('article', $slug->getValue());
    }

    public function testRejectTooLongSlug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Slug cannot exceed 250 characters');

        new Slug(str_repeat('a', 251));
    }

    public function testAcceptMaximumLengthSlug(): void
    {
        $slug = new Slug(str_repeat('a', 250));

        $this->assertSame(str_repeat('a', 250), $slug->getValue());
    }

    public function testCreateSlugWithValidFormat(): void
    {
        $slug = new Slug('this-is-a-great-article-title');
        $this->assertSame('this-is-a-great-article-title', $slug->getValue());
    }

    public function testCreateSlugWithNumbersIsValid(): void
    {
        $slug = new Slug('top-10-things-you-need-to-know-in-2024');
        $this->assertSame('top-10-things-you-need-to-know-in-2024', $slug->getValue());
    }

    public function testCreateSlugWithSingleWordIsValid(): void
    {
        $slug = new Slug('article');
        $this->assertSame('article', $slug->getValue());
    }

    public function testCreateSlugWithValidDashesIsValid(): void
    {
        $slug = new Slug('title-with-existing-dashes');
        $this->assertSame('title-with-existing-dashes', $slug->getValue());
    }

    public function testCreateSlugWithMixedAlphanumericIsValid(): void
    {
        $slug = new Slug('article-123-test');
        $this->assertSame('article-123-test', $slug->getValue());
    }
}
