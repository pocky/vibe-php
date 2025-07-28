<?php

declare(strict_types=1);

namespace App\Tests\Blog\Unit\Domain\Tag\Shared\ValueObject;

use App\Blog\Domain\Shared\Exception\ValidationException;
use App\Blog\Domain\Tag\Shared\ValueObject\TagName;
use PHPUnit\Framework\TestCase;

final class TagNameTest extends TestCase
{
    public function test_it_creates_valid_tag_name(): void
    {
        $tagName = new TagName('Technology');

        $this->assertSame('Technology', $tagName->getValue());
    }

    public function test_it_throws_exception_for_empty_name(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_name.empty');

        new TagName('');
    }

    public function test_it_throws_exception_for_name_too_short(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_name.too_short');

        new TagName('ab');
    }

    public function test_it_throws_exception_for_name_too_long(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_name.too_long');

        $longName = str_repeat('a', 101);
        new TagName($longName);
    }

    public function test_it_allows_names_with_letters_numbers_spaces_and_hyphens(): void
    {
        $validNames = [
            'Technology',
            'Web Development',
            'PHP-8',
            'Laravel 10',
            'Vue-3-Composition-API',
        ];

        foreach ($validNames as $validName) {
            $tagName = new TagName($validName);
            $this->assertSame($validName, $tagName->getValue());
        }
    }

    public function test_it_throws_exception_for_invalid_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('validation.tag_name.invalid_format');

        new TagName('Tag@Name');
    }

    public function test_it_trims_whitespace(): void
    {
        $tagName = new TagName('  Technology  ');

        $this->assertSame('Technology', $tagName->getValue());
    }

    public function test_it_normalizes_multiple_spaces(): void
    {
        $tagName = new TagName('Web   Development');

        $this->assertSame('Web Development', $tagName->getValue());
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $tagName1 = new TagName('Technology');
        $tagName2 = new TagName('Technology');

        $this->assertTrue($tagName1->equals($tagName2));
    }

    public function test_equals_returns_false_for_different_value(): void
    {
        $tagName1 = new TagName('Technology');
        $tagName2 = new TagName('Science');

        $this->assertFalse($tagName1->equals($tagName2));
    }

    public function test_to_string_returns_value(): void
    {
        $tagName = new TagName('Technology');

        $this->assertSame('Technology', (string) $tagName);
    }
}
