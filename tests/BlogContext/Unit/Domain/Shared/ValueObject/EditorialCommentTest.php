<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\ValueObject\EditorialComment;
use PHPUnit\Framework\TestCase;

final class EditorialCommentTest extends TestCase
{
    public function testCreateEditorialComment(): void
    {
        $comment = new EditorialComment(
            comment: 'This paragraph needs more details',
            selectedText: 'needs more details',
            positionStart: 150,
            positionEnd: 168
        );

        $this->assertSame('This paragraph needs more details', $comment->getComment());
        $this->assertSame('needs more details', $comment->getSelectedText());
        $this->assertSame(150, $comment->getPositionStart());
        $this->assertSame(168, $comment->getPositionEnd());
    }

    public function testCreateEditorialCommentWithoutSelection(): void
    {
        $comment = new EditorialComment(
            comment: 'General feedback about the article'
        );

        $this->assertSame('General feedback about the article', $comment->getComment());
        $this->assertNull($comment->getSelectedText());
        $this->assertNull($comment->getPositionStart());
        $this->assertNull($comment->getPositionEnd());
    }

    public function testEmptyCommentThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Editorial comment cannot be empty');

        new EditorialComment(comment: '');
    }

    public function testWhitespaceOnlyCommentThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Editorial comment cannot be empty');

        new EditorialComment(comment: '   ');
    }

    public function testCommentTooLongThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Editorial comment cannot exceed 2000 characters');

        $longComment = str_repeat('a', 2001);
        new EditorialComment(comment: $longComment);
    }

    public function testInvalidPositionRangeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Position end must be greater than position start');

        new EditorialComment(
            comment: 'Test comment',
            selectedText: 'test',
            positionStart: 100,
            positionEnd: 50
        );
    }

    public function testNegativePositionThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Position values must be non-negative');

        new EditorialComment(
            comment: 'Test comment',
            selectedText: 'test',
            positionStart: -10,
            positionEnd: 50
        );
    }

    public function testPartialSelectionDataThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Either provide all selection data or none');

        new EditorialComment(
            comment: 'Test comment',
            selectedText: 'test',
            positionStart: 100
            // Missing positionEnd
        );
    }

    public function testEquals(): void
    {
        $comment1 = new EditorialComment(
            comment: 'Same comment',
            selectedText: 'text',
            positionStart: 10,
            positionEnd: 14
        );

        $comment2 = new EditorialComment(
            comment: 'Same comment',
            selectedText: 'text',
            positionStart: 10,
            positionEnd: 14
        );

        $comment3 = new EditorialComment(
            comment: 'Different comment',
            selectedText: 'text',
            positionStart: 10,
            positionEnd: 14
        );

        $this->assertTrue($comment1->equals($comment2));
        $this->assertFalse($comment1->equals($comment3));
    }

    public function testHasSelection(): void
    {
        $commentWithSelection = new EditorialComment(
            comment: 'Test',
            selectedText: 'text',
            positionStart: 10,
            positionEnd: 14
        );

        $commentWithoutSelection = new EditorialComment(
            comment: 'Test'
        );

        $this->assertTrue($commentWithSelection->hasSelection());
        $this->assertFalse($commentWithoutSelection->hasSelection());
    }
}
