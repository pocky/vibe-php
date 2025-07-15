<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Domain\SubmitForReview;

use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Domain\SubmitForReview\DataPersister\Article;
use App\BlogContext\Domain\SubmitForReview\DataProvider\ArticleForReview;
use App\BlogContext\Domain\SubmitForReview\Event\ArticleSubmittedForReview;
use App\BlogContext\Domain\SubmitForReview\Exception\InvalidSubmissionException;
use App\BlogContext\Domain\SubmitForReview\Submitter;
use PHPUnit\Framework\TestCase;

final class SubmitterTest extends TestCase
{
    private Submitter $submitter;

    protected function setUp(): void
    {
        $this->submitter = new Submitter();
    }

    public function testSubmitDraftArticleForReview(): void
    {
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Test Article');

        $articleForReview = new ArticleForReview(
            articleId: $articleId,
            title: $title,
            status: ArticleStatus::DRAFT,
            submittedAt: new \DateTimeImmutable()
        );

        $article = ($this->submitter)($articleForReview);

        $this->assertInstanceOf(Article::class, $article);
        $this->assertSame($articleId, $article->getArticleId());
        $this->assertSame($title, $article->getTitle());
        $this->assertSame(ArticleStatus::PENDING_REVIEW, $article->getStatus());
        $this->assertNotNull($article->getSubmittedAt());

        // Check that event was created
        $events = $article->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleSubmittedForReview::class, $events[0]);

        $event = $events[0];
        $this->assertSame($articleId->getValue(), $event->articleId);
        $this->assertSame($title->getValue(), $event->title);
    }

    public function testSubmitRejectedArticleForReReview(): void
    {
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Previously Rejected Article');

        $articleForReview = new ArticleForReview(
            articleId: $articleId,
            title: $title,
            status: ArticleStatus::REJECTED,
            submittedAt: new \DateTimeImmutable()
        );

        $article = ($this->submitter)($articleForReview);

        $this->assertSame(ArticleStatus::PENDING_REVIEW, $article->getStatus());

        $events = $article->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(ArticleSubmittedForReview::class, $events[0]);
    }

    public function testCannotSubmitPendingReviewArticle(): void
    {
        $this->expectException(InvalidSubmissionException::class);
        $this->expectExceptionMessage('Article is already pending review');

        $articleForReview = new ArticleForReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            status: ArticleStatus::PENDING_REVIEW,
            submittedAt: new \DateTimeImmutable()
        );

        ($this->submitter)($articleForReview);
    }

    public function testCannotSubmitApprovedArticle(): void
    {
        $this->expectException(InvalidSubmissionException::class);
        $this->expectExceptionMessage('Article is already approved');

        $articleForReview = new ArticleForReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            status: ArticleStatus::APPROVED,
            submittedAt: new \DateTimeImmutable()
        );

        ($this->submitter)($articleForReview);
    }

    public function testCannotSubmitPublishedArticle(): void
    {
        $this->expectException(InvalidSubmissionException::class);
        $this->expectExceptionMessage('Cannot submit published article for review');

        $articleForReview = new ArticleForReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            status: ArticleStatus::PUBLISHED,
            submittedAt: new \DateTimeImmutable()
        );

        ($this->submitter)($articleForReview);
    }

    public function testCannotSubmitArchivedArticle(): void
    {
        $this->expectException(InvalidSubmissionException::class);
        $this->expectExceptionMessage('Cannot submit archived article for review');

        $articleForReview = new ArticleForReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            status: ArticleStatus::ARCHIVED,
            submittedAt: new \DateTimeImmutable()
        );

        ($this->submitter)($articleForReview);
    }

    public function testSubmittedAtTimestampIsPreserved(): void
    {
        $submittedAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $articleForReview = new ArticleForReview(
            articleId: new ArticleId('550e8400-e29b-41d4-a716-446655440000'),
            title: new Title('Test Article'),
            status: ArticleStatus::DRAFT,
            submittedAt: $submittedAt
        );

        $article = ($this->submitter)($articleForReview);

        $this->assertEquals($submittedAt, $article->getSubmittedAt());
    }
}
