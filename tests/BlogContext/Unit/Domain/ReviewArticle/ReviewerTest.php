<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\ReviewArticle;

use App\BlogContext\Domain\ReviewArticle\DataPersister\ReviewedArticle;
use App\BlogContext\Domain\ReviewArticle\DataProvider\ArticleReview;
use App\BlogContext\Domain\ReviewArticle\Event\ArticleApproved;
use App\BlogContext\Domain\ReviewArticle\Event\ArticleRejected;
use App\BlogContext\Domain\ReviewArticle\Exception\InvalidReviewException;
use App\BlogContext\Domain\ReviewArticle\Reviewer;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\ReviewDecision;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ReviewerTest extends TestCase
{
    private Reviewer $reviewer;

    protected function setUp(): void
    {
        $this->reviewer = new Reviewer();
    }

    public function testApproveArticle(): void
    {
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Article to Approve');
        $reviewerId = Uuid::v7()->toRfc4122();
        $decision = ReviewDecision::approve('Well written article, ready for publication');
        $reviewedAt = new \DateTimeImmutable();

        $articleReview = new ArticleReview(
            articleId: $articleId,
            title: $title,
            status: ArticleStatus::PENDING_REVIEW,
            reviewerId: $reviewerId,
            decision: $decision,
            reviewedAt: $reviewedAt
        );

        $reviewedArticle = ($this->reviewer)($articleReview);

        $this->assertInstanceOf(ReviewedArticle::class, $reviewedArticle);
        $this->assertSame($articleId, $reviewedArticle->getArticleId());
        $this->assertSame($title, $reviewedArticle->getTitle());
        $this->assertSame(ArticleStatus::APPROVED, $reviewedArticle->getStatus());
        $this->assertSame($reviewerId, $reviewedArticle->getReviewerId());
        $this->assertTrue($reviewedArticle->getDecision()->isApproved());
        $this->assertEquals($reviewedAt, $reviewedArticle->getReviewedAt());

        // Check event
        $events = $reviewedArticle->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleApproved::class, $events[0]);

        $event = $events[0];
        $this->assertSame($articleId->getValue(), $event->articleId);
        $this->assertSame($title->getValue(), $event->title);
        $this->assertSame($reviewerId, $event->reviewerId);
        $this->assertSame('Well written article, ready for publication', $event->approvalReason);
    }

    public function testRejectArticle(): void
    {
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Article to Reject');
        $reviewerId = Uuid::v7()->toRfc4122();
        $decision = ReviewDecision::reject('Needs significant improvements in structure and accuracy');
        $reviewedAt = new \DateTimeImmutable();

        $articleReview = new ArticleReview(
            articleId: $articleId,
            title: $title,
            status: ArticleStatus::PENDING_REVIEW,
            reviewerId: $reviewerId,
            decision: $decision,
            reviewedAt: $reviewedAt
        );

        $reviewedArticle = ($this->reviewer)($articleReview);

        $this->assertSame(ArticleStatus::REJECTED, $reviewedArticle->getStatus());
        $this->assertTrue($reviewedArticle->getDecision()->isRejected());

        // Check event
        $events = $reviewedArticle->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleRejected::class, $events[0]);

        $event = $events[0];
        $this->assertSame($articleId->getValue(), $event->articleId);
        $this->assertSame($title->getValue(), $event->title);
        $this->assertSame($reviewerId, $event->reviewerId);
        $this->assertSame('Needs significant improvements in structure and accuracy', $event->rejectionReason);
    }

    public function testCannotReviewDraftArticle(): void
    {
        $this->expectException(InvalidReviewException::class);
        $this->expectExceptionMessage('Cannot review article with status: draft');

        $articleReview = new ArticleReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Draft Article'),
            status: ArticleStatus::DRAFT,
            reviewerId: Uuid::v7()->toRfc4122(),
            decision: ReviewDecision::approve(),
            reviewedAt: new \DateTimeImmutable()
        );

        ($this->reviewer)($articleReview);
    }

    public function testCannotReviewAlreadyApprovedArticle(): void
    {
        $this->expectException(InvalidReviewException::class);
        $this->expectExceptionMessage('Article has already been approved');

        $articleReview = new ArticleReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Approved Article'),
            status: ArticleStatus::APPROVED,
            reviewerId: Uuid::v7()->toRfc4122(),
            decision: ReviewDecision::approve(),
            reviewedAt: new \DateTimeImmutable()
        );

        ($this->reviewer)($articleReview);
    }

    public function testCannotReviewPublishedArticle(): void
    {
        $this->expectException(InvalidReviewException::class);
        $this->expectExceptionMessage('Cannot review published article');

        $articleReview = new ArticleReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Published Article'),
            status: ArticleStatus::PUBLISHED,
            reviewerId: Uuid::v7()->toRfc4122(),
            decision: ReviewDecision::approve(),
            reviewedAt: new \DateTimeImmutable()
        );

        ($this->reviewer)($articleReview);
    }

    public function testCannotReviewArchivedArticle(): void
    {
        $this->expectException(InvalidReviewException::class);
        $this->expectExceptionMessage('Cannot review archived article');

        $articleReview = new ArticleReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Archived Article'),
            status: ArticleStatus::ARCHIVED,
            reviewerId: Uuid::v7()->toRfc4122(),
            decision: ReviewDecision::approve(),
            reviewedAt: new \DateTimeImmutable()
        );

        ($this->reviewer)($articleReview);
    }

    public function testReviewTimestampIsPreserved(): void
    {
        $reviewedAt = new \DateTimeImmutable('2024-01-15 14:30:00');

        $articleReview = new ArticleReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            status: ArticleStatus::PENDING_REVIEW,
            reviewerId: Uuid::v7()->toRfc4122(),
            decision: ReviewDecision::approve(),
            reviewedAt: $reviewedAt
        );

        $reviewedArticle = ($this->reviewer)($articleReview);

        $this->assertEquals($reviewedAt, $reviewedArticle->getReviewedAt());
    }

    public function testApprovalWithoutReason(): void
    {
        $articleReview = new ArticleReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            status: ArticleStatus::PENDING_REVIEW,
            reviewerId: Uuid::v7()->toRfc4122(),
            decision: ReviewDecision::approve(), // No reason provided
            reviewedAt: new \DateTimeImmutable()
        );

        $reviewedArticle = ($this->reviewer)($articleReview);

        $this->assertSame(ArticleStatus::APPROVED, $reviewedArticle->getStatus());
        $this->assertNull($reviewedArticle->getDecision()->getReason());
    }
}
