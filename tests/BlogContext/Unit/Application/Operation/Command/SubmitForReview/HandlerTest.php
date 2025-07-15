<?php

declare(strict_types=1);

namespace App\Tests\BlogContext\Unit\Application\Operation\Command\SubmitForReview;

use App\BlogContext\Application\Operation\Command\SubmitForReview\Command;
use App\BlogContext\Application\Operation\Command\SubmitForReview\Handler;
use App\BlogContext\Domain\Shared\Model\Article;
use App\BlogContext\Domain\Shared\Repository\ArticleRepositoryInterface;
use App\BlogContext\Domain\Shared\ValueObject\ArticleId;
use App\BlogContext\Domain\Shared\ValueObject\ArticleStatus;
use App\BlogContext\Domain\Shared\ValueObject\Content;
use App\BlogContext\Domain\Shared\ValueObject\Slug;
use App\BlogContext\Domain\Shared\ValueObject\Title;
use App\BlogContext\Domain\SubmitForReview\Event\ArticleSubmittedForReview;
use App\BlogContext\Domain\SubmitForReview\Submitter;
use App\Shared\Infrastructure\MessageBus\EventBusInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class HandlerTest extends TestCase
{
    private ArticleRepositoryInterface&MockObject $articleRepository;
    private EventBusInterface&MockObject $eventBus;
    private Submitter $submitter;
    private Handler $handler;

    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);
        $this->submitter = new Submitter();
        $this->handler = new Handler($this->articleRepository, $this->eventBus, $this->submitter);
    }

    public function testHandleSubmitForReviewCommand(): void
    {
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $title = new Title('Test Article');
        $slug = new Slug('test-article');
        $content = new Content('Article content');
        $createdAt = new \DateTimeImmutable();

        $article = new Article(
            id: $articleId,
            title: $title,
            content: $content,
            slug: $slug,
            status: ArticleStatus::DRAFT,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            authorId: '660e8400-e29b-41d4-a716-446655440001',
        );

        $this->articleRepository->expects($this->once())
            ->method('findById')
            ->with($articleId)
            ->willReturn($article);

        $this->articleRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn ($savedArticle) => ArticleStatus::PENDING_REVIEW === $savedArticle->getStatus()));

        $this->eventBus->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(fn ($event) => $event instanceof ArticleSubmittedForReview
                && '550e8400-e29b-41d4-a716-446655440000' === $event->articleId));

        ($this->handler)($command);
    }

    public function testHandleThrowsExceptionWhenArticleNotFound(): void
    {
        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            authorId: '660e8400-e29b-41d4-a716-446655440001',
        );

        $this->articleRepository->expects($this->once())
            ->method('findById')
            ->with($this->isInstanceOf(ArticleId::class))
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Article not found');

        ($this->handler)($command);
    }

    public function testHandleThrowsExceptionWhenArticleCannotBeSubmitted(): void
    {
        $articleId = new ArticleId('550e8400-e29b-41d4-a716-446655440000');
        $article = new Article(
            id: $articleId,
            title: new Title('Test Article'),
            content: new Content('This is a longer content that should pass the validation requirements'),
            slug: new Slug('test-article'),
            status: ArticleStatus::PUBLISHED, // Cannot submit published article
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $command = new Command(
            articleId: '550e8400-e29b-41d4-a716-446655440000',
            authorId: '660e8400-e29b-41d4-a716-446655440001',
        );

        $this->articleRepository->expects($this->once())
            ->method('findById')
            ->willReturn($article);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot submit published article for review');

        ($this->handler)($command);
    }
}
