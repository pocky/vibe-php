<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\ApproveArticle;

use App\BlogContext\Application\Gateway\ApproveArticle\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testCreateValidRequestWithReason(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            reason: 'Well written article'
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertSame('770e8400-e29b-41d4-a716-446655440002', $request->reviewerId);
        $this->assertSame('Well written article', $request->reason);
    }

    public function testCreateValidRequestWithoutReason(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            reason: null
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertSame('770e8400-e29b-41d4-a716-446655440002', $request->reviewerId);
        $this->assertNull($request->reason);
    }

    public function testFromDataWithValidData(): void
    {
        $data = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => 'Good content',
        ];

        $request = Request::fromData($data);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertSame('770e8400-e29b-41d4-a716-446655440002', $request->reviewerId);
        $this->assertSame('Good content', $request->reason);
    }

    public function testFromDataWithMissingArticleIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        Request::fromData([
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
        ]);
    }

    public function testFromDataWithMissingReviewerIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Reviewer ID is required');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
        ]);
    }

    public function testFromDataWithInvalidArticleIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid article ID format');

        Request::fromData([
            'articleId' => 'not-a-uuid',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
        ]);
    }

    public function testFromDataWithInvalidReviewerIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid reviewer ID format');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => 'not-a-uuid',
        ]);
    }

    public function testFromDataWithLongReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Approval reason cannot exceed 1000 characters');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => str_repeat('a', 1001),
        ]);
    }

    public function testDataSerialization(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            reason: 'Approved'
        );

        $data = $request->data();

        $this->assertSame([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => 'Approved',
        ], $data);
    }
}
