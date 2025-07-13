<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\UpdateArticle;

use App\BlogContext\Application\Gateway\UpdateArticle\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testCanCreateResponse(): void
    {
        $updatedAt = new \DateTimeImmutable('2024-01-15T10:30:00Z');
        $changedFields = ['title', 'content'];

        $response = new Response(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Updated Title',
            slug: 'updated-title',
            status: 'draft',
            updatedAt: $updatedAt,
            changedFields: $changedFields
        );

        self::assertSame('550e8400-e29b-41d4-a716-446655440000', $response->articleId);
        self::assertSame('Updated Title', $response->title);
        self::assertSame('updated-title', $response->slug);
        self::assertSame('draft', $response->status);
        self::assertSame($updatedAt, $response->updatedAt);
        self::assertSame($changedFields, $response->changedFields);
    }

    public function testCanConvertToDataArray(): void
    {
        $updatedAt = new \DateTimeImmutable('2024-01-15T10:30:00Z');
        $changedFields = ['title'];

        $response = new Response(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test Article',
            slug: 'test-article',
            status: 'published',
            updatedAt: $updatedAt,
            changedFields: $changedFields
        );

        $data = $response->data();

        $expected = [
            'articleId' => '550e8400-e29b-41d4-a716-446655440000',
            'title' => 'Test Article',
            'slug' => 'test-article',
            'status' => 'published',
            'updatedAt' => '2024-01-15T10:30:00+00:00',
            'changedFields' => ['title'],
        ];

        self::assertSame($expected, $data);
    }

    public function testCanCreateResponseWithEmptyChangedFields(): void
    {
        $response = new Response(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            slug: 'test',
            status: 'draft',
            updatedAt: new \DateTimeImmutable(),
            changedFields: []
        );

        self::assertSame([], $response->changedFields);
    }

    public function testResponseIsReadonly(): void
    {
        $response = new Response(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            slug: 'test',
            status: 'draft',
            updatedAt: new \DateTimeImmutable()
        );

        $reflection = new \ReflectionClass($response);
        self::assertTrue($reflection->isReadOnly(), 'Response should be readonly');
    }
}
