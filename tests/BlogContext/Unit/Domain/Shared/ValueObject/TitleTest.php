<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\Exception\ValidationException;
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
        $this->expectException(ValidationException::class);

        try {
            new Title('');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.title.empty', $e->getTranslationKey());
            throw $e;
        }
    }

    public function testRejectWhitespaceOnlyTitle(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new Title('   ');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.title.empty', $e->getTranslationKey());
            throw $e;
        }
    }

    public function testRejectTooShortTitle(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new Title('Hi');
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.title.too_short', $e->getTranslationKey());
            $this->assertEquals([
                'min_length' => 3,
                'actual_length' => 2,
            ], $e->getTranslationParameters());
            throw $e;
        }
    }

    public function testAcceptMinimumLengthTitle(): void
    {
        $title = new Title('Min');

        $this->assertSame('Min', $title->getValue());
    }

    public function testRejectTooLongTitle(): void
    {
        $this->expectException(ValidationException::class);

        try {
            new Title(str_repeat('a', 201));
        } catch (ValidationException $e) {
            $this->assertEquals('validation.article.title.too_long', $e->getTranslationKey());
            $this->assertEquals([
                'max_length' => 200,
                'actual_length' => 201,
            ], $e->getTranslationParameters());
            throw $e;
        }
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
