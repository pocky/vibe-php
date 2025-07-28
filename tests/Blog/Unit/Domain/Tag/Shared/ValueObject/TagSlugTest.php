<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Tag\Shared\ValueObject;

use App\Blog\Domain\Shared\Exception\ValidationException;
use App\Blog\Domain\Tag\Shared\ValueObject\TagSlug;
use PHPUnit\Framework\TestCase;

final class TagSlugTest extends TestCase
{
    public function test_it_creates_valid_tag_slug(): void
    {
        $tagSlug = new TagSlug('technology');

        $this->assertSame('technology', $tagSlug->getValue());
    }

    public function test_it_throws_exception_for_empty_slug(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.empty');

        new TagSlug('');
    }

    public function test_it_throws_exception_for_slug_too_short(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.too_short');

        new TagSlug('ab');
    }

    public function test_it_throws_exception_for_slug_too_long(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.too_long');

        $longSlug = str_repeat('a', 101);
        new TagSlug($longSlug);
    }

    public function test_it_allows_slugs_with_letters_numbers_and_hyphens(): void
    {
        $validSlugs = [
            'technology',
            'web-development',
            'php-8',
            'laravel-10',
            'vue-3-composition-api',
        ];

        foreach ($validSlugs as $validSlug) {
            $tagSlug = new TagSlug($validSlug);
            $this->assertSame($validSlug, $tagSlug->getValue());
        }
    }

    public function test_it_throws_exception_for_invalid_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.invalid_format');

        new TagSlug('tag_slug');
    }

    public function test_it_throws_exception_for_uppercase_letters(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.invalid_format');

        new TagSlug('Technology');
    }

    public function test_it_throws_exception_for_spaces(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.invalid_format');

        new TagSlug('web development');
    }

    public function test_it_throws_exception_for_leading_hyphen(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.invalid_format');

        new TagSlug('-technology');
    }

    public function test_it_throws_exception_for_trailing_hyphen(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.invalid_format');

        new TagSlug('technology-');
    }

    public function test_it_throws_exception_for_consecutive_hyphens(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.invalid_format');

        new TagSlug('web--development');
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $slug1 = new TagSlug('technology');
        $slug2 = new TagSlug('technology');

        $this->assertTrue($slug1->equals($slug2));
    }

    public function test_equals_returns_false_for_different_value(): void
    {
        $slug1 = new TagSlug('technology');
        $slug2 = new TagSlug('science');

        $this->assertFalse($slug1->equals($slug2));
    }

    public function test_to_string_returns_value(): void
    {
        $tagSlug = new TagSlug('technology');

        $this->assertSame('technology', (string) $tagSlug);
    }

    public function test_generate_from_name_creates_valid_slug(): void
    {
        $testCases = [
            'Technology' => 'technology',
            'Web Development' => 'web-development',
            'PHP 8' => 'php-8',
            'Laravel 10' => 'laravel-10',
            'Vue.js 3' => 'vue-js-3',
            'C++ Programming' => 'c-programming',
            '  Trimmed  Spaces  ' => 'trimmed-spaces',
            'Special@#$Characters' => 'special-characters',
            'Multiple   Spaces' => 'multiple-spaces',
            'UPPERCASE LETTERS' => 'uppercase-letters',
            'café' => 'cafe',
            'naïve' => 'naive',
            'résumé' => 'resume',
        ];

        foreach ($testCases as $name => $expectedSlug) {
            $slug = TagSlug::generateFromName($name);
            $this->assertSame($expectedSlug, $slug->getValue());
        }
    }

    public function test_generate_from_name_throws_exception_for_empty_result(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_slug.empty');

        TagSlug::generateFromName('@#$%');
    }
}
