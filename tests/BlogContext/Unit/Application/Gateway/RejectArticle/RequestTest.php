<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\RejectArticle;

use App\BlogContext\Application\Gateway\RejectArticle\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testCreateValidRequest(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            reason: 'Needs significant improvements'
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertSame('770e8400-e29b-41d4-a716-446655440002', $request->reviewerId);
        $this->assertSame('Needs significant improvements', $request->reason);
    }

    public function testFromDataWithValidData(): void
    {
        $data = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => 'Content needs work',
        ];

        $request = Request::fromData($data);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertSame('770e8400-e29b-41d4-a716-446655440002', $request->reviewerId);
        $this->assertSame('Content needs work', $request->reason);
    }

    public function testFromDataWithMissingReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason is required');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
        ]);
    }

    public function testFromDataWithEmptyReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason cannot be empty');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => '',
        ]);
    }

    public function testFromDataWithWhitespaceReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason cannot be empty');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => '   ',
        ]);
    }

    public function testFromDataWithLongReasonThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection reason cannot exceed 1000 characters');

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
            reason: 'Rejected for improvements'
        );

        $data = $request->data();

        $this->assertSame([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'reviewerId' => '770e8400-e29b-41d4-a716-446655440002',
            'reason' => 'Rejected for improvements',
        ], $data);
    }
}
