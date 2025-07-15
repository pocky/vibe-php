<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\SubmitForReview;

use App\BlogContext\Application\Gateway\SubmitForReview\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testCreateValidRequest(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            authorId: '660e8400-e29b-41d4-a716-446655440001'
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertSame('660e8400-e29b-41d4-a716-446655440001', $request->authorId);
    }

    public function testCreateRequestWithoutAuthor(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            authorId: null
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertNull($request->authorId);
    }

    public function testFromDataWithValidData(): void
    {
        $data = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'authorId' => '660e8400-e29b-41d4-a716-446655440001',
        ];

        $request = Request::fromData($data);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertSame('660e8400-e29b-41d4-a716-446655440001', $request->authorId);
    }

    public function testFromDataWithoutAuthorId(): void
    {
        $data = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
        ];

        $request = Request::fromData($data);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        $this->assertNull($request->authorId);
    }

    public function testFromDataWithMissingArticleIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        Request::fromData([]);
    }

    public function testFromDataWithEmptyArticleIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        Request::fromData([
            'articleId' => '',
        ]);
    }

    public function testFromDataWithInvalidArticleIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid article ID format');

        Request::fromData([
            'articleId' => 'not-a-uuid',
        ]);
    }

    public function testFromDataWithInvalidAuthorIdThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid author ID format');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'authorId' => 'not-a-uuid',
        ]);
    }

    public function testDataSerialization(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            authorId: '660e8400-e29b-41d4-a716-446655440001'
        );

        $data = $request->data();

        $this->assertSame([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'authorId' => '660e8400-e29b-41d4-a716-446655440001',
        ], $data);
    }

    public function testDataSerializationWithoutAuthor(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            authorId: null
        );

        $data = $request->data();

        $this->assertSame([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'authorId' => null,
        ], $data);
    }
}
