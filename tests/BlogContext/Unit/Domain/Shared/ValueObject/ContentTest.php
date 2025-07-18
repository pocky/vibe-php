<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use PHPUnit\Framework\TestCase;

final class ContentTest extends TestCase
{
    public function testCreateValidContent(): void
    {
        $content = new Content('This is valid article content with enough text.');

        $this->assertSame('This is valid article content with enough text.', $content->getValue());
    }

    public function testContentEquality(): void
    {
        $content1 = new Content('Same content text');
        $content2 = new Content('Same content text');
        $content3 = new Content('Different content text');

        $this->assertTrue($content1->equals($content2));
        $this->assertFalse($content1->equals($content3));
    }

    public function testRejectEmptyContent(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new Content('');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.content.empty', $e->getTranslationKey());
            throw $e;
        }
    }

    public function testRejectWhitespaceOnlyContent(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new Content('   ');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.content.empty', $e->getTranslationKey());
            throw $e;
        }
    }

    public function testRejectTooShortContent(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new Content('Hi');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.content.too_short', $e->getTranslationKey());
            $this->assertEquals([
                'min_length' => 10,
                'actual_length' => 2,
            ], $e->getTranslationParameters());
            throw $e;
        }
    }

    public function testAcceptMinimumLengthContent(): void
    {
        $content = new Content('Hello test');

        $this->assertSame('Hello test', $content->getValue());
    }

    public function testAcceptLongContent(): void
    {
        $longContent = str_repeat('This is a longer content with multiple sentences. ', 50);
        $content = new Content($longContent);

        $this->assertSame($longContent, $content->getValue());
    }

    public function testContentWithMarkup(): void
    {
        $markupContent = '<p>This is content with <strong>HTML markup</strong> and <em>formatting</em>.</p>';
        $content = new Content($markupContent);

        $this->assertSame($markupContent, $content->getValue());
    }
}
