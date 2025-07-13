<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\UpdateArticle;

use App\BlogContext\Application\Gateway\UpdateArticle\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    public function testCanCreateValidRequest(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Updated Article Title',
            content: 'This is the updated content of the article with sufficient length.'
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        self::assertSame('Updated Article Title', $request->title);
        self::assertSame('This is the updated content of the article with sufficient length.', $request->content);
    }

    public function testCanCreateFromDataArray(): void
    {
        $data = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'title' => 'Test Article',
            'content' => 'This is test content with sufficient length.',
        ];

        $request = Request::fromData($data);

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $request->articleId);
        self::assertSame('Test Article', $request->title);
        self::assertSame('This is test content with sufficient length.', $request->content);
    }

    public function testCanConvertToDataArray(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test Article',
            content: 'Test content'
        );

        $data = $request->data();

        $expected = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'title' => 'Test Article',
            'content' => 'Test content',
        ];

        self::assertSame($expected, $data);
    }

    public function testFromDataThrowsExceptionForMissingArticleId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Article ID is required');

        Request::fromData([
            'title' => 'Test',
            'content' => 'Content',
        ]);
    }

    public function testFromDataThrowsExceptionForMissingTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title is required');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'content' => 'Content',
        ]);
    }

    public function testFromDataThrowsExceptionForMissingContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Content is required');

        Request::fromData([
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'title' => 'Test',
        ]);
    }

    public function testRequestIsReadonly(): void
    {
        $request = new Request(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            content: 'Content'
        );

        $reflection = new \ReflectionClass($request);
        self::assertTrue($reflection->isReadOnly(), 'Request should be readonly');
    }
}
