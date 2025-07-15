<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\SubmitForReview\Event;

use App\BlogContext\Domain\SubmitForReview\Event\ArticleSubmittedForReview;
use PHPUnit\Framework\TestCase;

final class ArticleSubmittedForReviewTest extends TestCase
{
    public function testCreateEvent(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';
        $title = 'Article Title';
        $authorId = '660e8400-e29b-41d4-a716-446655440001';
        $submittedAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $event = new ArticleSubmittedForReview(
            articleId: $articleId,
            title: $title,
            authorId: $authorId,
            submittedAt: $submittedAt
        );

        $this->assertSame($articleId, $event->articleId);
        $this->assertSame($title, $event->title);
        $this->assertSame($authorId, $event->authorId);
        $this->assertEquals($submittedAt, $event->submittedAt);
    }

    public function testEventName(): void
    {
        $event = new ArticleSubmittedForReview(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            authorId: '660e8400-e29b-41d4-a716-446655440001',
            submittedAt: new \DateTimeImmutable()
        );

        $this->assertSame('blog.article.submitted_for_review', $event->getName());
    }

    public function testEventPayload(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';
        $title = 'Article Title';
        $authorId = '660e8400-e29b-41d4-a716-446655440001';
        $submittedAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $event = new ArticleSubmittedForReview(
            articleId: $articleId,
            title: $title,
            authorId: $authorId,
            submittedAt: $submittedAt
        );

        $payload = $event->getPayload();

        $this->assertArrayHasKey('articleId', $payload);
        $this->assertArrayHasKey('title', $payload);
        $this->assertArrayHasKey('authorId', $payload);
        $this->assertArrayHasKey('submittedAt', $payload);

        $this->assertSame($articleId, $payload['articleId']);
        $this->assertSame($title, $payload['title']);
        $this->assertSame($authorId, $payload['authorId']);
        $this->assertSame('2024-01-15T10:30:00+00:00', $payload['submittedAt']);
    }

    public function testEventOccurredAt(): void
    {
        $event = new ArticleSubmittedForReview(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            title: 'Test',
            authorId: '660e8400-e29b-41d4-a716-446655440001',
            submittedAt: new \DateTimeImmutable()
        );

        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getOccurredAt());
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $event->getOccurredAt());
    }

    public function testEventAggregateId(): void
    {
        $articleId = '550e8400-e29b-41d4-a716-446655440000';

        $event = new ArticleSubmittedForReview(
            articleId: $articleId,
            title: 'Test',
            authorId: '660e8400-e29b-41d4-a716-446655440001',
            submittedAt: new \DateTimeImmutable()
        );

        $this->assertSame($articleId, $event->getAggregateId());
    }
}
