<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Gateway\SubmitForReview;

use App\BlogContext\Application\Gateway\SubmitForReview\Response;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    public function testCreateResponse(): void
    {
        $submittedAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $response = new Response(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            status: 'pending_review',
            submittedAt: $submittedAt
        );

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $response->articleId);
        $this->assertSame('pending_review', $response->status);
        $this->assertEquals($submittedAt, $response->submittedAt);
    }

    public function testDataSerialization(): void
    {
        $submittedAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $response = new Response(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            status: 'pending_review',
            submittedAt: $submittedAt
        );

        $data = $response->data();

        $this->assertArrayHasKey('articleId', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('submittedAt', $data);

        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $data['articleId']);
        $this->assertSame('pending_review', $data['status']);
        $this->assertSame('2024-01-15T10:30:00+00:00', $data['submittedAt']);
    }

    public function testResponseIsReadonly(): void
    {
        $response = new Response(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            status: 'pending_review',
            submittedAt: new \DateTimeImmutable()
        );

        // This test ensures the class is designed as readonly
        $reflection = new \ReflectionClass($response);
        $this->assertTrue($reflection->isReadOnly());
    }
}
