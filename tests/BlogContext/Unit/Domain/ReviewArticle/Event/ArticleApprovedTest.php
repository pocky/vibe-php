<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\ReviewArticle\Event;

use App\BlogContext\Domain\ReviewArticle\Event\ArticleApproved;
use PHPUnit\Framework\TestCase;

final class ArticleApprovedTest extends TestCase
{
    public function testCreateEvent(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';
        $title = 'Approved Article';
        $reviewerId = '770e8400-e29b-41d4-a716-446655440002';
        $approvalReason = 'Well written and informative';
        $reviewedAt = new \DateTimeImmutable('2024-01-15 14:30:00');

        $event = new ArticleApproved(
            articleId: $articleId,
            title: $title,
            reviewerId: $reviewerId,
            approvalReason: $approvalReason,
            reviewedAt: $reviewedAt
        );

        $this->assertSame($articleId, $event->articleId);
        $this->assertSame($title, $event->title);
        $this->assertSame($reviewerId, $event->reviewerId);
        $this->assertSame($approvalReason, $event->approvalReason);
        $this->assertEquals($reviewedAt, $event->reviewedAt);
    }

    public function testCreateEventWithoutReason(): void
    {
        $event = new ArticleApproved(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Approved Article',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            approvalReason: null,
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertNull($event->approvalReason);
    }

    public function testEventName(): void
    {
        $event = new ArticleApproved(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            approvalReason: null,
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertSame('blog.article.approved', $event->getName());
    }

    public function testEventPayload(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';
        $title = 'Approved Article';
        $reviewerId = '770e8400-e29b-41d4-a716-446655440002';
        $approvalReason = 'Excellent content';
        $reviewedAt = new \DateTimeImmutable('2024-01-15 14:30:00');

        $event = new ArticleApproved(
            articleId: $articleId,
            title: $title,
            reviewerId: $reviewerId,
            approvalReason: $approvalReason,
            reviewedAt: $reviewedAt
        );

        $payload = $event->getPayload();

        $this->assertArrayHasKey('articleId', $payload);
        $this->assertArrayHasKey('title', $payload);
        $this->assertArrayHasKey('reviewerId', $payload);
        $this->assertArrayHasKey('approvalReason', $payload);
        $this->assertArrayHasKey('reviewedAt', $payload);

        $this->assertSame($articleId, $payload['articleId']);
        $this->assertSame($title, $payload['title']);
        $this->assertSame($reviewerId, $payload['reviewerId']);
        $this->assertSame($approvalReason, $payload['approvalReason']);
        $this->assertSame('2024-01-15T14:30:00+00:00', $payload['reviewedAt']);
    }

    public function testEventOccurredAt(): void
    {
        $event = new ArticleApproved(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            approvalReason: null,
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredAt());
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $event->getOccurredAt());
    }

    public function testEventAggregateId(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';

        $event = new ArticleApproved(
            articleId: $articleId,
            title: 'Test',
            reviewerId: '770e8400-e29b-41d4-a716-446655440002',
            approvalReason: null,
            reviewedAt: new \DateTimeImmutable()
        );

        $this->assertSame($articleId, $event->getAggregateId());
    }
}
