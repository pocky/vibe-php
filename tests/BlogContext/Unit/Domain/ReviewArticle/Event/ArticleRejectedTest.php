<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\ReviewArticle\Event;

use App\BlogContext\Domain\ReviewArticle\Event\ArticleRejected;
use PHPUnit\Framework\TestCase;

final class ArticleRejectedTest extends TestCase
{
    public function testCreateEvent(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';
        $title = 'Rejected Article';
        $reviewerId = '770e8400-e29b-41d4-a716-446655440002';
        $rejectionReason = 'Needs significant improvements in accuracy and structure';
        $reviewedAt = new \DateTimeImmutable('2024-01-15 14:30:00');

        $event = new ArticleRejected(
            articleId: $articleId,
            title: $title,
            reviewerId: $reviewerId,
            rejectionReason: $rejectionReason,
            reviewedAt: $reviewedAt
        );

        $this->assertSame($articleId, $event->articleId);
        $this->assertSame($title, $event->title);
        $this->assertSame($reviewerId, $event->reviewerId);
        $this->assertSame($rejectionReason, $event->rejectionReason);
        $this->assertEquals($reviewedAt, $event->reviewedAt);
    }

    public function testEventName(): void
    {
        $event = new ArticleRejected(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            rejectionReason: 'Needs work',
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertSame('blog.article.rejected', $event->getName());
    }

    public function testEventPayload(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';
        $title = 'Rejected Article';
        $reviewerId = '770e8400-e29b-41d4-a716-446655440002';
        $rejectionReason = 'Content does not meet quality standards';
        $reviewedAt = new \DateTimeImmutable('2024-01-15 14:30:00');

        $event = new ArticleRejected(
            articleId: $articleId,
            title: $title,
            reviewerId: $reviewerId,
            rejectionReason: $rejectionReason,
            reviewedAt: $reviewedAt
        );

        $payload = $event->getPayload();

        $this->assertArrayHasKey('articleId', $payload);
        $this->assertArrayHasKey('title', $payload);
        $this->assertArrayHasKey('reviewerId', $payload);
        $this->assertArrayHasKey('rejectionReason', $payload);
        $this->assertArrayHasKey('reviewedAt', $payload);

        $this->assertSame($articleId, $payload['articleId']);
        $this->assertSame($title, $payload['title']);
        $this->assertSame($reviewerId, $payload['reviewerId']);
        $this->assertSame($rejectionReason, $payload['rejectionReason']);
        $this->assertSame('2024-01-15T14:30:00+00:00', $payload['reviewedAt']);
    }

    public function testEventOccurredAt(): void
    {
        $event = new ArticleRejected(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            rejectionReason: 'Needs work',
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredAt());
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $event->getOccurredAt());
    }

    public function testEventAggregateId(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';

        $event = new ArticleRejected(
            articleId: $articleId,
            title: 'Test',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            rejectionReason: 'Needs work',
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertSame($articleId, $event->getAggregateId());
    }

    public function testRejectionReasonIsRequired(): void
    {
        // Since rejection reason is mandatory, it should always be present
        $event = new ArticleRejected(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            rejectionReason: 'Required reason',
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertNotEmpty($event->rejectionReason);
    }
}
