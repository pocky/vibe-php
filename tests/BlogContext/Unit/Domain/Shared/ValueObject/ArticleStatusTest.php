<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\Shared\ValueObject;

use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use PHPUnit\Framework\TestCase;

final class ArticleStatusTest extends TestCase
{
    public function testDraftStatus(): void
    {
        $status = ArticleStatus::DRAFT;

        $this->assertTrue($status->isDraft());
        $this->assertFalse($status->isPublished());
        $this->assertFalse($status->isArchived());
        $this->assertSame('draft', $status->getValue());
    }

    public function testPublishedStatus(): void
    {
        $status = ArticleStatus::PUBLISHED;

        $this->assertFalse($status->isDraft());
        $this->assertTrue($status->isPublished());
        $this->assertFalse($status->isArchived());
        $this->assertSame('published', $status->getValue());
    }

    public function testArchivedStatus(): void
    {
        $status = ArticleStatus::ARCHIVED;

        $this->assertFalse($status->isDraft());
        $this->assertFalse($status->isPublished());
        $this->assertTrue($status->isArchived());
        $this->assertSame('archived', $status->getValue());
    }

    public function testCreateFromString(): void
    {
        $status = ArticleStatus::fromString('draft');
        $this->assertSame(ArticleStatus::DRAFT, $status);

        $status = ArticleStatus::fromString('published');
        $this->assertSame(ArticleStatus::PUBLISHED, $status);

        $status = ArticleStatus::fromString('archived');
        $this->assertSame(ArticleStatus::ARCHIVED, $status);
    }

    public function testCreateFromInvalidString(): void
    {
        $this->expectException(\ValueError::class);

        ArticleStatus::fromString('invalid');
    }

    public function testStatusEquality(): void
    {
        $status1 = ArticleStatus::DRAFT;
        $status2 = ArticleStatus::DRAFT;
        $status3 = ArticleStatus::PUBLISHED;

        $this->assertTrue($status1->equals($status2));
        $this->assertFalse($status1->equals($status3));
    }

    public function testGetAllCases(): void
    {
        $cases = ArticleStatus::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(ArticleStatus::DRAFT, $cases);
        $this->assertContains(ArticleStatus::PENDING_REVIEW, $cases);
        $this->assertContains(ArticleStatus::APPROVED, $cases);
        $this->assertContains(ArticleStatus::REJECTED, $cases);
        $this->assertContains(ArticleStatus::PUBLISHED, $cases);
        $this->assertContains(ArticleStatus::ARCHIVED, $cases);
    }

    public function testPendingReviewStatus(): void
    {
        $status = ArticleStatus::PENDING_REVIEW;

        $this->assertFalse($status->isDraft());
        $this->assertTrue($status->isPendingReview());
        $this->assertFalse($status->isApproved());
        $this->assertFalse($status->isRejected());
        $this->assertFalse($status->isPublished());
        $this->assertFalse($status->isArchived());
        $this->assertSame('pending_review', $status->getValue());
    }

    public function testApprovedStatus(): void
    {
        $status = ArticleStatus::APPROVED;

        $this->assertFalse($status->isDraft());
        $this->assertFalse($status->isPendingReview());
        $this->assertTrue($status->isApproved());
        $this->assertFalse($status->isRejected());
        $this->assertFalse($status->isPublished());
        $this->assertFalse($status->isArchived());
        $this->assertSame('approved', $status->getValue());
    }

    public function testRejectedStatus(): void
    {
        $status = ArticleStatus::REJECTED;

        $this->assertFalse($status->isDraft());
        $this->assertFalse($status->isPendingReview());
        $this->assertFalse($status->isApproved());
        $this->assertTrue($status->isRejected());
        $this->assertFalse($status->isPublished());
        $this->assertFalse($status->isArchived());
        $this->assertSame('rejected', $status->getValue());
    }

    public function testCreateReviewStatusesFromString(): void
    {
        $status = ArticleStatus::fromString('pending_review');
        $this->assertSame(ArticleStatus::PENDING_REVIEW, $status);

        $status = ArticleStatus::fromString('approved');
        $this->assertSame(ArticleStatus::APPROVED, $status);

        $status = ArticleStatus::fromString('rejected');
        $this->assertSame(ArticleStatus::REJECTED, $status);
    }

    public function testCanBeSubmittedForReview(): void
    {
        $this->assertTrue(ArticleStatus::DRAFT->canBeSubmittedForReview());
        $this->assertTrue(ArticleStatus::REJECTED->canBeSubmittedForReview());

        $this->assertFalse(ArticleStatus::PENDING_REVIEW->canBeSubmittedForReview());
        $this->assertFalse(ArticleStatus::APPROVED->canBeSubmittedForReview());
        $this->assertFalse(ArticleStatus::PUBLISHED->canBeSubmittedForReview());
        $this->assertFalse(ArticleStatus::ARCHIVED->canBeSubmittedForReview());
    }

    public function testCanBeReviewed(): void
    {
        $this->assertTrue(ArticleStatus::PENDING_REVIEW->canBeReviewed());

        $this->assertFalse(ArticleStatus::DRAFT->canBeReviewed());
        $this->assertFalse(ArticleStatus::APPROVED->canBeReviewed());
        $this->assertFalse(ArticleStatus::REJECTED->canBeReviewed());
        $this->assertFalse(ArticleStatus::PUBLISHED->canBeReviewed());
        $this->assertFalse(ArticleStatus::ARCHIVED->canBeReviewed());
    }

    public function testCanBePublished(): void
    {
        $this->assertTrue(ArticleStatus::APPROVED->canBePublished());

        $this->assertFalse(ArticleStatus::DRAFT->canBePublished());
        $this->assertFalse(ArticleStatus::PENDING_REVIEW->canBePublished());
        $this->assertFalse(ArticleStatus::REJECTED->canBePublished());
        $this->assertFalse(ArticleStatus::PUBLISHED->canBePublished());
        $this->assertFalse(ArticleStatus::ARCHIVED->canBePublished());
    }
}
